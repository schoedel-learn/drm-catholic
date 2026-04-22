<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class PublicFormController extends Controller
{
    public function show(string $token)
    {
        $form = Form::where('token', $token)->firstOrFail();

        if (!$form->isAccessible()) {
            return Inertia::render('PublicForm/Expired', [
                'message' => $form->isExpired() ? 'This form has expired.' : 'This form is no longer accepting responses.',
            ]);
        }

        // Get the custom fields for this form
        $customFields = CustomField::where('jurisdiction_id', $form->jurisdiction_id)
            ->where('entity_type', $form->entity_type)
            ->whereIn('key', $form->fields)
            ->orderBy('order')
            ->get();

        return Inertia::render('PublicForm/Show', [
            'form' => [
                'name' => $form->name,
                'description' => $form->description,
                'entity_type' => $form->entity_type,
                'token' => $form->token,
            ],
            'fields' => $customFields,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $form = Form::where('token', $token)->firstOrFail();

        if (!$form->isAccessible()) {
            return back()->withErrors(['form' => 'This form is no longer accepting responses.']);
        }

        // Rate limiting: 10 submissions per hour per IP
        $key = 'form-submit:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors(['form' => 'Too many submissions. Please try again later.']);
        }
        RateLimiter::hit($key, 3600);

        // Honeypot check (bot protection)
        if ($request->filled('website_url')) {
            // Silently reject bot submissions
            return Inertia::render('PublicForm/ThankYou', [
                'formName' => $form->name,
            ]);
        }

        // Validate required fields
        $customFields = CustomField::where('jurisdiction_id', $form->jurisdiction_id)
            ->where('entity_type', $form->entity_type)
            ->whereIn('key', $form->fields)
            ->get();

        $rules = [
            'submitter_email' => 'required|email',
            'submitter_name' => 'required|string|max:255',
        ];

        foreach ($customFields as $field) {
            if ($field->required) {
                $rules["data.{$field->key}"] = 'required';
            }
        }

        $validated = $request->validate($rules);

        // Create submission
        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'submitter_email' => $validated['submitter_email'],
            'submitter_name' => $validated['submitter_name'],
            'data' => $request->input('data', []),
            'submitted_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        // Process submission to update/create Contact or Organization
        app(\App\Services\FormSubmissionProcessor::class)->process($submission);

        return Inertia::render('PublicForm/ThankYou', [
            'formName' => $form->name,
        ]);
    }
}
