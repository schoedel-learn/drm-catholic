<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Jurisdiction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DioceseController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'include_external' => ['sometimes', 'boolean'],
        ]);

        $limit = $validated['limit'] ?? 10;
        $includeExternal = (bool) ($validated['include_external'] ?? false);
        $query = strtolower($validated['query']);

        $builder = Jurisdiction::query()
            ->whereIn('type', ['diocese', 'archdiocese', 'eparchy', 'archeparchy'])
            ->whereRaw('LOWER(name) LIKE ?', ['%'.$query.'%']);

        if (! $includeExternal) {
            $builder->where('is_external', false);
        }

        $results = $builder
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'type', 'state', 'city', 'is_external', 'locked'])
            ->map(fn (Jurisdiction $j) => [
                'id' => (string) $j->id,
                'name' => (string) $j->name,
                'type' => (string) $j->type,
                'state' => (string) $j->state,
                'city' => (string) $j->city,
                'is_external' => (bool) $j->is_external,
                'locked' => (bool) $j->locked,
            ])
            ->all();

        return response()->json([
            'query' => $validated['query'],
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
