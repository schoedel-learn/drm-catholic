<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_contact_form_and_create_contact()
    {
        // 1. Setup
        $diocese = Jurisdiction::factory()->create();
        $form = Form::create([
            'jurisdiction_id' => $diocese->id,
            'name' => 'New Parishioner Form',
            'entity_type' => 'contact',
            'fields' => ['phone'],
            'token' => 'test-token-123',
            'is_active' => true,
        ]);

        // 2. Submit Form
        $response = $this->post(route('public-form.submit', $form->token), [
            'submitter_email' => 'john.doe@example.com',
            'submitter_name' => 'John Doe',
            'data' => [
                'phone' => '555-0199',
            ],
        ]);

        // 3. Verify
        $response->assertStatus(200); // Or redirect, depending on controller response. It renders Inertia.

        // Check Submission
        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'submitter_email' => 'john.doe@example.com',
        ]);

        // Check Contact Created
        // Note: We must check without global scope or act as a user in that jurisdiction, 
        // but here we can just use Database assertions which usually hit the DB directly.
        $this->assertDatabaseHas('contacts', [
            'jurisdiction_id' => $diocese->id,
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '555-0199',
        ]);

        // Verify Linkage
        $contact = Contact::withoutGlobalScopes()->where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($contact, 'Contact was not created');
        $this->assertDatabaseHas('form_submissions', [
            'entity_id' => $contact->id,
        ]);
    }

    public function test_can_submit_contact_form_and_update_existing_contact()
    {
        // 1. Setup
        $diocese = Jurisdiction::factory()->create();
        $existingContact = Contact::create([
            'jurisdiction_id' => $diocese->id,
            'owner_diocese_id' => $diocese->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'role' => 'parishioner',
            'email' => 'jane.doe@example.com', // KEY
        ]);

        $form = Form::create([
            'jurisdiction_id' => $diocese->id,
            'name' => 'Update Info Form',
            'entity_type' => 'contact',
            'fields' => ['phone'],
            'token' => 'test-token-456',
            'is_active' => true,
        ]);

        // 2. Submit Form
        $this->post(route('public-form.submit', $form->token), [
            'submitter_email' => 'jane.doe@example.com',
            'submitter_name' => 'Jane Updated', // Name shouldn't change core name fields unless I added that logic?
            // Logic says: if (isset($data['first_name'])) update. We didn't pass first_name in data.
            // But we do update core if custom fields present? No, only specific fields.
            // Wait, my logic allows updating 'first_name', 'last_name', 'phone' if in $data.
            // Let's test phone update.
            'data' => [
                'phone' => '555-9999',
            ],
        ]);

        // 3. Verify
        $this->assertDatabaseHas('contacts', [
            'id' => $existingContact->id,
            'email' => 'jane.doe@example.com',
            'phone' => '555-9999', // Updated
        ]);
    }
}
