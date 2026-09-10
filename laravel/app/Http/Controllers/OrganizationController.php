<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\EntityType;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organization::class);

        $entityTypes = $this->organizationEntityTypes($request);

        $organizations = Organization::query()
            ->with('entityType')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'ilike', '%'.$search.'%');
            })
            ->when($request->entity_type_id, function ($query, $entityTypeId) {
                $query->where('entity_type_id', $entityTypeId);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
            'filters' => $request->only(['search', 'entity_type_id']),
            'entityTypes' => $entityTypes,
            'userPermissions' => $this->getUserPermissions($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Organization::class);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        $customFields = CustomField::query()
            ->where('jurisdiction_id', $tenantId)
            ->where('entity_type', 'organization')
            ->orderBy('order')
            ->get();

        return Inertia::render('Organizations/Create', [
            'customFields' => $customFields,
            'entityTypes' => $this->organizationEntityTypes($request),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Organization::class);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        $customFields = CustomField::query()
            ->where('jurisdiction_id', $tenantId)
            ->where('entity_type', 'organization')
            ->get();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'entity_type_id' => [
                'required',
                Rule::exists('entity_types', 'id')->where(fn ($query) => $query
                    ->where('jurisdiction_id', $tenantId)
                    ->where('base_entity', EntityType::BASE_ORGANIZATION)
                    ->where('is_active', true)),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'address.street' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.zip' => ['nullable', 'string'],
            'google_place_id' => ['nullable', 'string'],
            'google_formatted_address' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'string'],
            'google_lat' => ['nullable', 'numeric'],
            'google_lng' => ['nullable', 'numeric'],
        ];

        foreach ($customFields as $field) {
            $fieldRules = $field->required ? ['required'] : ['nullable'];
            if ($field->type === 'number') {
                $fieldRules[] = 'numeric';
            }
            if ($field->type === 'date') {
                $fieldRules[] = 'date';
            }
            $rules['custom_data.'.$field->key] = $fieldRules;
        }

        $validated = $request->validate($rules);

        Organization::create($validated);

        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $organization->load(['jurisdiction', 'entityType']);

        $customFields = CustomField::query()
            ->where('jurisdiction_id', $organization->jurisdiction_id)
            ->where('entity_type', 'organization')
            ->get();

        return Inertia::render('Organizations/Show', [
            'organization' => $organization,
            'customFields' => $customFields,
            'userPermissions' => $this->getUserPermissions($request),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Organization $organization): Response
    {
        $this->authorize('update', $organization);

        $customFields = CustomField::query()
            ->where('jurisdiction_id', $organization->jurisdiction_id)
            ->where('entity_type', 'organization')
            ->orderBy('order')
            ->get();

        return Inertia::render('Organizations/Edit', [
            'organization' => $organization,
            'customFields' => $customFields,
            'entityTypes' => $this->organizationEntityTypes($request),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        $customFields = CustomField::query()
            ->where('jurisdiction_id', $tenantId)
            ->where('entity_type', 'organization')
            ->get();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'entity_type_id' => [
                'required',
                Rule::exists('entity_types', 'id')->where(fn ($query) => $query
                    ->where('jurisdiction_id', $tenantId)
                    ->where('base_entity', EntityType::BASE_ORGANIZATION)
                    ->where('is_active', true)),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'address.street' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.zip' => ['nullable', 'string'],
            'google_place_id' => ['nullable', 'string'],
            'google_formatted_address' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'string'],
            'google_lat' => ['nullable', 'numeric'],
            'google_lng' => ['nullable', 'numeric'],
        ];

        foreach ($customFields as $field) {
            $fieldRules = $field->required ? ['required'] : ['nullable'];
            if ($field->type === 'number') {
                $fieldRules[] = 'numeric';
            }
            if ($field->type === 'date') {
                $fieldRules[] = 'date';
            }
            $rules['custom_data.'.$field->key] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $organization->update($validated);

        return redirect()->back()->with('success', 'Organization updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return redirect()->route('organizations.index')->with('success', 'Organization deleted successfully.');
    }

    /**
     * Get user permissions for frontend.
     */
    private function getUserPermissions(Request $request): array
    {
        $team = $request->user()->currentTeam;

        return [
            'canCreate' => $request->user()->hasTeamPermission($team, 'create'),
            'canUpdate' => $request->user()->hasTeamPermission($team, 'update'),
            'canDelete' => $request->user()->hasTeamPermission($team, 'delete'),
        ];
    }

    private function organizationEntityTypes(Request $request)
    {
        $jurisdiction = $request->user()->currentTeam?->jurisdiction;

        if ($jurisdiction) {
            EntityType::seedDefaults($jurisdiction);
        }

        return EntityType::query()
            ->where('base_entity', EntityType::BASE_ORGANIZATION)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
}
