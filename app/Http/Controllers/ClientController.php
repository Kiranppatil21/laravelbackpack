<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientTax;
use App\Models\Company;
use App\Models\Designation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(): Response
    {
        $clients = Client::with(['agency', 'company', 'contacts', 'taxes'])
            ->paginate(15);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): Response
    {
        $companies = Company::select('id', 'name')->get();
        $agencies = Agency::select('id', 'name')->get();
        $designations = Designation::select('id', 'name')->get();
        $nextSerialNo = Client::getNextSerialNumber();

        return Inertia::render('Clients/Create', [
            'companies' => $companies,
            'agencies' => $agencies,
            'designations' => $designations,
            'nextSerialNo' => $nextSerialNo,
            'taxTypes' => ClientTax::getTaxTypes(),
            'taxStatuses' => ClientTax::getStatusOptions(),
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Basic client information
            'company_id' => 'nullable|exists:companies,id',
            'name_of_client' => 'required|string|max:191',
            'to_title' => 'nullable|string|max:20',
            'site_name' => 'required|string|max:191',
            'address' => 'required|string',
            'dob' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            
            // Contact information
            'contact_no_1' => 'required|string|max:20',
            'contact_no_2' => 'nullable|string|max:20',
            'site_supervisor_contact' => 'nullable|string|max:20',
            'site_admin_contact' => 'required|string|max:20',
            'site_manager_contact' => 'required|string|max:20',
            
            // Tax and business information
            'gst_no' => 'nullable|string|max:30',
            'tds_percentage' => 'nullable|numeric|min:0|max:100',
            'pan_no' => 'nullable|string|max:20',
            
            // Email information
            'primary_email_1' => 'required|email|max:191|unique:clients,primary_email_1',
            'primary_email_2' => 'nullable|email|max:191',
            
            // Additional charges
            'additional_charges' => 'nullable|numeric|min:0',
            'additional_charges_comment' => 'nullable|string',
            
            // Authentication
            'password' => ['required', 'confirmed', Password::min(8)],
            
            // Notification preferences
            'sms_reports' => 'boolean',
            'sms_attendance' => 'boolean',
            'sms_bill' => 'boolean',
            'sms_bill_reminder' => 'boolean',
            'sms_payment_receipt' => 'boolean',
            'email_reports' => 'boolean',
            'email_attendance' => 'boolean',
            'email_bill' => 'boolean',
            'email_bill_reminder' => 'boolean',
            'email_payment_receipt' => 'boolean',
            
            // Dynamic contacts array
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required_with:contacts|string|max:191',
            'contacts.*.contact_no' => 'required_with:contacts|string|max:20',
            'contacts.*.designation_id' => 'nullable|exists:designations,id',
            'contacts.*.email' => 'nullable|email|max:191',
            'contacts.*.send_sms' => 'boolean',
            'contacts.*.send_email' => 'boolean',
            
            // Dynamic taxes array
            'taxes' => 'nullable|array',
            'taxes.*.tax_status' => 'required_with:taxes|in:active,inactive,applicable',
            'taxes.*.tax_type' => 'required_with:taxes|string|max:50',
            'taxes.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'taxes.*.tax_number' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Create the main client record
            $client = Client::create([
                'company_id' => $validated['company_id'],
                'name_of_client' => $validated['name_of_client'],
                'to_title' => $validated['to_title'],
                'site_name' => $validated['site_name'],
                'address' => $validated['address'],
                'dob' => $validated['dob'],
                'date_of_anniversary' => $validated['date_of_anniversary'],
                'contact_no_1' => $validated['contact_no_1'],
                'contact_no_2' => $validated['contact_no_2'],
                'site_supervisor_contact' => $validated['site_supervisor_contact'],
                'site_admin_contact' => $validated['site_admin_contact'],
                'site_manager_contact' => $validated['site_manager_contact'],
                'gst_no' => $validated['gst_no'],
                'tds_percentage' => $validated['tds_percentage'],
                'pan_no' => $validated['pan_no'],
                'primary_email_1' => $validated['primary_email_1'],
                'primary_email_2' => $validated['primary_email_2'],
                'additional_charges' => $validated['additional_charges'],
                'additional_charges_comment' => $validated['additional_charges_comment'],
                'password' => Hash::make($validated['password']),
                'sms_reports' => $validated['sms_reports'] ?? false,
                'sms_attendance' => $validated['sms_attendance'] ?? false,
                'sms_bill' => $validated['sms_bill'] ?? false,
                'sms_bill_reminder' => $validated['sms_bill_reminder'] ?? false,
                'sms_payment_receipt' => $validated['sms_payment_receipt'] ?? false,
                'email_reports' => $validated['email_reports'] ?? false,
                'email_attendance' => $validated['email_attendance'] ?? false,
                'email_bill' => $validated['email_bill'] ?? false,
                'email_bill_reminder' => $validated['email_bill_reminder'] ?? false,
                'email_payment_receipt' => $validated['email_payment_receipt'] ?? false,
            ]);

            // Create contact records if provided
            if (!empty($validated['contacts'])) {
                foreach ($validated['contacts'] as $contactData) {
                    ClientContact::create([
                        'client_id' => $client->id,
                        'name' => $contactData['name'],
                        'contact_no' => $contactData['contact_no'],
                        'designation_id' => $contactData['designation_id'] ?? null,
                        'email' => $contactData['email'] ?? null,
                        'send_sms' => $contactData['send_sms'] ?? false,
                        'send_email' => $contactData['send_email'] ?? false,
                    ]);
                }
            }

            // Create tax records if provided
            if (!empty($validated['taxes'])) {
                foreach ($validated['taxes'] as $taxData) {
                    ClientTax::create([
                        'client_id' => $client->id,
                        'tax_status' => $taxData['tax_status'],
                        'tax_type' => $taxData['tax_type'],
                        'tax_percent' => $taxData['tax_percent'] ?? null,
                        'tax_number' => $taxData['tax_number'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('clients.index')
                ->with('success', 'Client created successfully! Serial No: ' . $client->serial_no);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client): Response
    {
        $client->load(['company', 'contacts.designation', 'taxes']);

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client): Response
    {
        $client->load(['contacts.designation', 'taxes']);
        $companies = Company::select('id', 'name')->get();
        $designations = Designation::select('id', 'name')->get();

        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'companies' => $companies,
            'designations' => $designations,
            'taxTypes' => ClientTax::getTaxTypes(),
            'taxStatuses' => ClientTax::getStatusOptions(),
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            // Same validation rules as store, but excluding unique email check for current client
            'company_id' => 'nullable|exists:companies,id',
            'name_of_client' => 'required|string|max:191',
            'to_title' => 'nullable|string|max:20',
            'site_name' => 'required|string|max:191',
            'address' => 'required|string',
            'dob' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            'contact_no_1' => 'required|string|max:20',
            'contact_no_2' => 'nullable|string|max:20',
            'site_supervisor_contact' => 'nullable|string|max:20',
            'site_admin_contact' => 'required|string|max:20',
            'site_manager_contact' => 'required|string|max:20',
            'gst_no' => 'nullable|string|max:30',
            'tds_percentage' => 'nullable|numeric|min:0|max:100',
            'pan_no' => 'nullable|string|max:20',
            'primary_email_1' => 'required|email|max:191|unique:clients,primary_email_1,' . $client->id,
            'primary_email_2' => 'nullable|email|max:191',
            'additional_charges' => 'nullable|numeric|min:0',
            'additional_charges_comment' => 'nullable|string',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            // ... other validation rules same as store method
        ]);

        try {
            DB::beginTransaction();

            // Update password only if provided
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $client->update($validated);

            DB::commit();

            return redirect()->route('clients.show', $client)
                ->with('success', 'Client updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update client: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        try {
            $client->delete();

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete client: ' . $e->getMessage());
        }
    }
}