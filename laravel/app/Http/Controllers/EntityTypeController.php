<?php

namespace App\Http\Controllers;

use App\Models\EntityType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntityTypeController extends Controller
{
    /**
     * Display a listing of entity types.
     */
    public function index()
    {
        $entityTypes = EntityType::query()
            ->orderBy('base_entity')
            ->orderBy('name')
            ->get();

        return Inertia::render('EntityTypes/Index', [
            'entityTypes' => $entityTypes,
            'baseEntityOptions' => [
                ['value' => EntityType::BASE_CONTACT, 'label' => 'Contact'],
                ['value' => EntityType::BASE_ORGANIZATION, 'label' => 'Organization'],
                ['value' => EntityType::BASE_STANDALONE, 'label' => 'Standalone'],
            ],
        ]);
    }

    /**
     * Store a newly created entity type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_entity' => 'required|in:contact,organization,standalone',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        EntityType::create($validated);

        return back()->with('success', 'Entity type created successfully.');
    }

    /**
     * Update the specified entity type.
     */
    public function update(Request $request, EntityType $entityType)
    {
        if ($entityType->is_system) {
            return back()->with('error', 'System entity types cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $entityType->update($validated);

        return back()->with('success', 'Entity type updated successfully.');
    }

    /**
     * Remove the specified entity type.
     */
    public function destroy(EntityType $entityType)
    {
        if ($entityType->is_system) {
            return back()->with('error', 'System entity types cannot be deleted.');
        }

        // Check if any entities are using this type
        $contactCount = $entityType->contacts()->count();
        $orgCount = $entityType->organizations()->count();

        if ($contactCount > 0 || $orgCount > 0) {
            return back()->with('error', "Cannot delete: {$contactCount} contacts and {$orgCount} organizations are using this type.");
        }

        $entityType->delete();

        return back()->with('success', 'Entity type deleted successfully.');
    }
}
