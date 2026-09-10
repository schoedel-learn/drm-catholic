<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Contact::class);

        return Inertia::render('Contacts/Index', [
            'contacts' => Contact::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
            'userPermissions' => $this->getUserPermissions($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Contact::class);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        return Inertia::render('Contacts/Create', [
            'customFields' => CustomField::where('jurisdiction_id', $tenantId)
                ->where('entity_type', 'contact')
                ->orderBy('order')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Contact::class);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'custom_data' => 'nullable|array',
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Contact $contact)
    {
        $this->authorize('view', $contact);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
            'customFields' => CustomField::where('jurisdiction_id', $tenantId)
                ->where('entity_type', 'contact')
                ->orderBy('order')
                ->get(),
            'userPermissions' => $this->getUserPermissions($request),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $tenantId = $request->user()->currentTeam->jurisdiction_id;

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'customFields' => CustomField::where('jurisdiction_id', $tenantId)
                ->where('entity_type', 'contact')
                ->orderBy('order')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'custom_data' => 'nullable|array',
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact deleted successfully.');
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
}
