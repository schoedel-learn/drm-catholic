<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Form;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        return Inertia::render('Forms/Index', [
            'forms' => Form::where('jurisdiction_id', $tenantId)
                ->withCount('submissions')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        return Inertia::render('Forms/Create', [
            'customFields' => CustomField::where('jurisdiction_id', $tenantId)
                ->orderBy('entity_type')
                ->orderBy('order')
                ->get()
                ->groupBy('entity_type'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entity_type' => 'required|in:contact,organization',
            'fields' => 'required|array|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $form = Form::create([
            'jurisdiction_id' => $request->user()->currentTeam->jurisdiction_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'entity_type' => $validated['entity_type'],
            'fields' => $validated['fields'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()->route('forms.show', $form)
            ->with('success', 'Form created successfully!');
    }

    public function show(Request $request, Form $form)
    {
        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        if ($form->jurisdiction_id !== $tenantId) {
            abort(403);
        }

        return Inertia::render('Forms/Show', [
            'form' => $form->load('submissions'),
            'customFields' => CustomField::where('jurisdiction_id', $tenantId)
                ->where('entity_type', $form->entity_type)
                ->get()
                ->keyBy('key'),
        ]);
    }

    public function destroy(Request $request, Form $form)
    {
        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        if ($form->jurisdiction_id !== $tenantId) {
            abort(403);
        }

        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Form deleted successfully!');
    }

    public function toggleActive(Request $request, Form $form)
    {
        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        if ($form->jurisdiction_id !== $tenantId) {
            abort(403);
        }

        $form->update(['is_active' => ! $form->is_active]);

        return back()->with('success', $form->is_active ? 'Form activated!' : 'Form deactivated!');
    }
}
