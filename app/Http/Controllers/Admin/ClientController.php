<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Client, Company, Designation, Agency};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(): Response
    {
        $clients = Client::with(['agency', 'company', 'contacts', 'taxes'])
            ->paginate(15);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        // Simple test first - return basic JSON to verify route works
        if (request()->has('test')) {
            return response()->json([
                'message' => 'Controller is working',
                'inertia_enabled' => class_exists('Inertia\\Inertia'),
                'companies_count' => Company::count(),
                'agencies_count' => Agency::count(),
            ]);
        }
        
        // Use Backpack admin blade template instead of Inertia
        $companies = Company::select('id', 'name')->get();
        $agencies = Agency::select('id', 'name')->get();
        $designations = Designation::select('id', 'name')->get();
        
        return view('admin.client.create', [
            'companies' => $companies,
            'agencies' => $agencies,
            'designations' => $designations,
            'nextSerialNo' => Client::getNextSerialNumber(),
            'taxTypes' => Client::getTaxTypes(),
            'taxStatuses' => Client::getTaxStatuses(),
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        // Basic validation rules for the form
        $rules = [
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'name_of_client' => 'required|string|max:255',
            'to_title' => 'nullable|string|max:50',
            'site_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'dob' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            'contact_no_1' => 'nullable|string|max:20',
            'contact_no_2' => 'nullable|string|max:20',
            'site_supervisor_contact' => 'nullable|string|max:20',
            'site_admin_contact' => 'nullable|string|max:20',
            'site_manager_contact' => 'nullable|string|max:20',
            'gst_no' => 'nullable|string|max:20',
            'tds_percentage' => 'nullable|numeric|min:0|max:100',
            'pan_no' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'serial_no' => 'nullable|integer',
        ];

        $validated = $request->validate($rules);

        try {
            // Set defaults
            $validated['tenant_uuid'] = function_exists('tenant') && tenant() ? tenant()->uuid : null;
            $validated['status'] = 'active';
            
            // Hash password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            // Create the client
            $client = Client::create($validated);

            // Success message and redirect
            return redirect()->route('client.create-custom')
                ->with('success', 'Client created successfully! Client ID: ' . $client->id);

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create client: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        $client->load(['agency', 'company']);
        
        return view('admin.client.show', [
            'client' => $client,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        $companies = Company::select('id', 'name')->get();
        $agencies = Agency::select('id', 'name')->get();
        $designations = Designation::select('id', 'name')->get();
        
        return view('admin.client.edit', [
            'client' => $client,
            'companies' => $companies,
            'agencies' => $agencies,
            'designations' => $designations,
            'taxTypes' => Client::getTaxTypes(),
            'taxStatuses' => Client::getTaxStatuses(),
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        // Basic validation rules for the update
        $rules = [
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'name_of_client' => 'required|string|max:255',
            'to_title' => 'nullable|string|max:50',
            'site_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'dob' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            'contact_no_1' => 'nullable|string|max:20',
            'contact_no_2' => 'nullable|string|max:20',
            'site_supervisor_contact' => 'nullable|string|max:20',
            'site_admin_contact' => 'nullable|string|max:20',
            'site_manager_contact' => 'nullable|string|max:20',
            'gst_no' => 'nullable|string|max:20',
            'tds_percentage' => 'nullable|numeric|min:0|max:100',
            'pan_no' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'serial_no' => 'nullable|integer',
        ];

        $validated = $request->validate($rules);

        try {
            // Hash password if provided
            if (!empty($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Update the client
            $client->update($validated);

            return redirect()->route('client.create-custom')
                ->with('success', 'Client updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update client: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        DB::beginTransaction();
        
        try {
            // Delete related contacts and taxes
            $client->contacts()->delete();
            $client->taxes()->delete();
            
            // Delete the client
            $client->delete();
            
            DB::commit();
            
            return redirect()
                ->route('backpack.client.index')
                ->with('success', 'Client deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors(['error' => 'Failed to delete client: ' . $e->getMessage()]);
        }
    }

    /**
     * Get client contacts
     */
    public function contacts(Client $client)
    {
        $contacts = $client->contacts()->with('designation')->get();
        
        return response()->json($contacts);
    }

    /**
     * Get client tax details
     */
    public function taxes(Client $client)
    {
        $taxes = $client->taxes()->get();
        
        return response()->json($taxes);
    }
}