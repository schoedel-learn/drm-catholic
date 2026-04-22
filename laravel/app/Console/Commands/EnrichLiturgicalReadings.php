<?php

namespace App\Console\Commands;

use App\Models\LiturgicalCalendarEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EnrichLiturgicalReadings extends Command
{
    protected $signature = 'liturgy:enrich-readings
                            {--date= : Enrich a specific date (YYYY-MM-DD)}
                            {--from= : Start date for range (YYYY-MM-DD)}
                            {--to= : End date for range (YYYY-MM-DD)}
                            {--force : Re-enrich events that already have structured readings}
                            {--dry-run : Show what would be fetched without updating}';

    protected $description = 'Fetch structured readings (including psalms) from USCCB and update liturgical calendar events';

    public function handle(): int
    {
        $events = $this->getEventsToProcess();

        if ($events->isEmpty()) {
            $this->info('No events to process.');
            return self::SUCCESS;
        }

        $this->info("Processing {$events->count()} liturgical events...");
        $bar = $this->output->createProgressBar($events->count());

        $enriched = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($events as $event) {
            $bar->advance();

            // Skip if already enriched (unless --force)
            if (!$this->option('force') && $this->isAlreadyEnriched($event)) {
                $skipped++;
                continue;
            }

            $url = $this->buildUsccbUrl($event->date);

            try {
                $html = $this->fetchPage($url);
                if (!$html) {
                    $this->newLine();
                    $this->warn("  Empty response for {$event->date->format('Y-m-d')} ({$url})");
                    $failed++;
                    continue;
                }

                $structured = $this->parseReadings($html);

                if (empty($structured)) {
                    $this->newLine();
                    $this->warn("  Could not parse readings for {$event->date->format('Y-m-d')}");
                    $failed++;
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->newLine();
                    $this->line("  [DRY RUN] {$event->date->format('Y-m-d')}: " . count($structured) . " readings found");
                    foreach ($structured as $r) {
                        $refrain = isset($r['refrain']) ? " — R. {$r['refrain']}" : '';
                        $this->line("    {$r['type']}: {$r['citation']}{$refrain}");
                    }
                    $enriched++;
                    continue;
                }

                // Merge structured readings into existing readings JSON
                $readings = $event->readings ?? [];
                $readings['structured'] = $structured;
                $event->readings = $readings;
                $event->save();

                $enriched++;

                // Rate limit: 200ms between requests
                usleep(200_000);

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  Error for {$event->date->format('Y-m-d')}: {$e->getMessage()}");
                $failed++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Done! Enriched: {$enriched}, Skipped: {$skipped}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function getEventsToProcess()
    {
        $query = LiturgicalCalendarEvent::query()->orderBy('date');

        if ($date = $this->option('date')) {
            $query->whereDate('date', $date);
        } elseif ($from = $this->option('from')) {
            $query->whereDate('date', '>=', $from);
            if ($to = $this->option('to')) {
                $query->whereDate('date', '<=', $to);
            }
        }

        return $query->get();
    }

    private function isAlreadyEnriched(LiturgicalCalendarEvent $event): bool
    {
        return !empty($event->readings['structured']);
    }

    private function buildUsccbUrl(Carbon $date): string
    {
        return 'https://bible.usccb.org/bible/readings/' . $date->format('mdy') . '.cfm';
    }

    private function fetchPage(string $url): ?string
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Catholic.Work Liturgical Calendar Enrichment)',
                'Accept' => 'text/html',
            ])
            ->get($url);

        if ($response->successful()) {
            return $response->body();
        }

        return null;
    }

    /**
     * Parse the USCCB readings page HTML into structured reading data.
     *
     * Actual USCCB DOM structure:
     *   <div class="innerblock">
     *     <div class="content-header">
     *       <h3 class="name">Reading 1</h3>
     *       <div class="address"><a href="...">Book Ch:Vs</a></div>
     *     </div>
     *     <div class="content-body">
     *       <p>Full text... (for psalms: R. (verse) <strong>refrain</strong>)</p>
     *     </div>
     *   </div>
     */
    private function parseReadings(string $html): array
    {
        $structured = [];

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find all innerblock containers — each holds one reading section
        $blocks = $xpath->query('//div[contains(@class, "innerblock")]');

        foreach ($blocks as $block) {
            // Find the h3 with class "name" inside content-header
            $h3List = $xpath->query('.//div[contains(@class, "content-header")]//h3[contains(@class, "name")]', $block);
            if ($h3List->length === 0) continue;

            $headerText = trim($h3List->item(0)->textContent);
            $type = $this->classifyReadingType($headerText);
            if (!$type) continue;

            // Extract citation link from div.address
            $addressLinks = $xpath->query('.//div[contains(@class, "address")]//a', $block);
            $citation = null;
            $bibleUrl = null;

            foreach ($addressLinks as $link) {
                $href = trim($link->getAttribute('href'));
                if (Str::contains($href, 'bible.usccb.org/bible/') && !Str::contains($href, '/readings/')) {
                    $citation = trim($link->textContent);
                    $bibleUrl = $href;
                    break;
                }
            }

            if (!$citation) continue;

            $entry = [
                'type' => $type,
                'citation' => $citation,
                'bible_url' => $bibleUrl,
            ];

            // For Responsorial Psalm, extract the refrain from <strong> in content-body
            if ($type === 'psalm') {
                $refrain = $this->extractPsalmRefrain($xpath, $block);
                if ($refrain) {
                    $entry['refrain'] = $refrain;
                }
            }

            $structured[] = $entry;
        }

        return $structured;
    }

    /**
     * Classify a heading into a reading type.
     */
    private function classifyReadingType(string $header): ?string
    {
        $header = strtolower(trim($header));

        if (Str::contains($header, 'responsorial psalm')) return 'psalm';
        if (Str::contains($header, 'gospel'))             return 'gospel';
        if (Str::contains($header, 'alleluia'))           return 'alleluia';
        if (Str::contains($header, 'reading 2'))          return 'reading_2';
        if (Str::contains($header, 'reading 1'))          return 'reading_1';
        if ($header === 'reading')                         return 'reading_1';

        return null;
    }

    /**
     * Extract the psalm refrain from the first <strong> tag inside content-body.
     *
     * USCCB HTML: <p>R. (12b) <strong>Lord, teach me your statutes.</strong><br>...</p>
     */
    private function extractPsalmRefrain(\DOMXPath $xpath, \DOMNode $block): ?string
    {
        $strongNodes = $xpath->query('.//div[contains(@class, "content-body")]//strong', $block);

        if ($strongNodes->length > 0) {
            $refrain = trim($strongNodes->item(0)->textContent);
            // Remove trailing period if present
            $refrain = rtrim($refrain, '.');
            return $refrain;
        }

        return null;
    }
}
