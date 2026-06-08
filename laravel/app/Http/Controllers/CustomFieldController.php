<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Settings/CustomFields', [
            'customFields' => CustomField::orderBy('order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,date,select,email',
            'entity_type' => 'required|string|in:contact,organization',
            'options' => 'nullable|array', // For select type
            'required' => 'boolean',
        ]);

        // Generate key from label (slug)
        $key = Str::slug($validated['label'], '_');

        // Ensure key is unique for this tenant AND entity type
        $originalKey = $key;
        $counter = 1;
        while (CustomField::where('key', $key)->where('entity_type', $validated['entity_type'])->exists()) {
            $key = $originalKey . '_' . $counter++;
        }

        CustomField::create([
            'jurisdiction_id' => $request->user()->currentTeam->jurisdiction_id,
            'entity_type' => $validated['entity_type'],
            'label' => $validated['label'],
            'key' => $key,
            'type' => $validated['type'],
            'options' => $validated['options'],
            'required' => $validated['required'] ?? false,
            'order' => CustomField::where('entity_type', $validated['entity_type'])->max('order') + 1,
        ]);

        return redirect()->back()->with('success', 'Custom field created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomField $customField)
    {
        $customField->delete();

        return redirect()->back()->with('success', 'Custom field deleted successfully.');
    }
}
