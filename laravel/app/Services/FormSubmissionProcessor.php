<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\FormSubmission;
use App\Models\Organization;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormSubmissionProcessor
{
    /**
     * Process a form submission to update or create the underlying entity.
     */
    public function process(FormSubmission $submission): void
    {
        $form = $submission->form;

        // We must perform matching within the correct jurisdiction context
        // and bypass the TenantScope which relies on auth user (since this is a guest action)

        DB::transaction(function () use ($submission, $form) {
            $entity = match ($form->entity_type) {
                'contact' => $this->processContact($submission),
                'organization' => $this->processOrganization($submission),
                default => null,
            };

            if ($entity) {
                $submission->update(['entity_id' => $entity->id]);
            }
        });
    }

    protected function processContact(FormSubmission $submission): Model
    {
        $form = $submission->form;
        $data = $submission->data;
        $email = $submission->submitter_email;
        $name = $submission->submitter_name;

        // 1. Find or Create Contact
        // Bypassing Global Scope to search by jurisdiction_id manually
        $contact = Contact::withoutGlobalScope(TenantScope::class)
            ->where('owner_diocese_id', $form->jurisdiction_id)
            ->where('email', $email)
            ->first();

        if (!$contact) {
            // Split name if possible
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';

            $contact = new Contact();
            $contact->id = (string) Str::uuid();
            $contact->jurisdiction_id = $form->jurisdiction_id;
            $contact->owner_diocese_id = $form->jurisdiction_id; // Default ownership
            $contact->first_name = $firstName;
            $contact->last_name = $lastName;
            $contact->role = 'Unassigned'; // Required field
            $contact->email = $email;
        }

        // 2. Update Basic Fields if present in form data
        if (isset($data['first_name']))
            $contact->first_name = $data['first_name'];
        if (isset($data['last_name']))
            $contact->last_name = $data['last_name'];
        if (isset($data['phone']))
            $contact->phone = $data['phone'];

        // 3. Merge Custom Fields
        $customData = $contact->custom_data ?? [];
        foreach ($data as $key => $value) {
            // Skip core fields we handled above
            if (in_array($key, ['first_name', 'last_name', 'phone', 'email'])) {
                continue;
            }
            $customData[$key] = $value;
        }
        $contact->custom_data = $customData;

        $contact->save(); // Use saveQuietly to avoid triggering Observers that might expect an auth user

        return $contact;
    }

    protected function processOrganization(FormSubmission $submission): Model
    {
        $form = $submission->form;
        $data = $submission->data;
        $email = $submission->submitter_email; // Often the contact person's email

        // Search by email is weaker for Org, but if 'website' or 'name' is in data, we could use that.
        // For now, sticking to the requirements: "self-identify method where users provide their email"
        // Ideally Organizations have an email field.

        $org = Organization::withoutGlobalScope(TenantScope::class)
            ->where('jurisdiction_id', $form->jurisdiction_id)
            ->where('email', $email)
            ->first();

        if (!$org) {
            $org = new Organization();
            $org->id = (string) Str::uuid();
            $org->jurisdiction_id = $form->jurisdiction_id;
            $org->name = $submission->submitter_name; // Defaulting org name to submitter name if new? Or should be distinct?
            // Usually for Org forms, there's a specific 'Organization Name' field.
            // If the form has a field mapping to 'name', we'll overwrite this below.
            $org->email = $email;
        }

        // Update Basic Fields
        if (isset($data['name']))
            $org->name = $data['name'];
        if (isset($data['phone']))
            $org->phone = $data['phone'];
        if (isset($data['website']))
            $org->website = $data['website'];
        if (isset($data['address']))
            $org->address = $data['address'];

        // Merge Custom Fields
        $customData = $org->custom_data ?? [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'phone', 'website', 'address', 'email'])) {
                continue;
            }
            $customData[$key] = $value;
        }
        $org->custom_data = $customData;

        $org->save();

        return $org;
    }
}
