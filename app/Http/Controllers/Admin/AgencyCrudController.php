<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AgencyRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Class AgencyCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 * @method mixed store()
 * @method mixed update()
 * @method mixed destroy($id = null)
 */
class AgencyCrudController extends CrudController
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Agency::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/agency');
        CRUD::setEntityNameStrings('agency', 'agencies');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        // Define visible columns including new fields
        $this->crud->addColumn(['name' => 'name', 'label' => 'Agency Name', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'is_active', 'label' => 'Active', 'type' => 'boolean']);
        $this->crud->addColumn(['name' => 'email', 'label' => 'Company Email', 'type' => 'email']);
        $this->crud->addColumn(['name' => 'phone', 'label' => 'Company Phone', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'gst_number', 'label' => 'GST', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'pan_number', 'label' => 'PAN', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'contact_person_name', 'label' => 'Contact Name', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'contact_person_email', 'label' => 'Contact Email', 'type' => 'email']);
        $this->crud->addColumn(['name' => 'contact_person_phone', 'label' => 'Contact Phone', 'type' => 'text']);
    $this->crud->addColumn(['name' => 'services', 'label' => 'Services', 'type' => 'text']);

    // Per-row toggle button for super-admins (view-based, JS does in-place update)
    // place in the action column (end of line buttons)
    $this->crud->addButtonFromView('line', 'toggle_active', 'vendor.backpack.crud.buttons.toggle_active', 'end');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AgencyRequest::class);

        // Company Details header
        $this->crud->addField([
            'name' => 'company_section',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mt-3 mb-2">Company Details</h4>',
        ]);

        // Row 1 (4 columns)
        // Agency name with Select2 autocomplete (allows new tags)
        $entry = $this->crud->getCurrentEntry();
        $currentName = ($entry instanceof \Illuminate\Database\Eloquent\Model) ? $entry->name : '';
        $nameSelect = '';
        $nameSelect .= '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
        $nameSelect .= '<label for="agency_name_select" class="form-label">Agency Name</label>';
        $nameSelect .= '<select id="agency_name_select" name="name" class="form-control agency-name-select2" style="width:100%">';
        if ($currentName) {
            $escaped = htmlspecialchars($currentName, ENT_QUOTES, 'UTF-8');
            $nameSelect .= "<option value=\"{$escaped}\" selected>" . $escaped . "</option>";
        }
        $nameSelect .= '</select>';
        $nameSelect .= <<<'HTML'
    <script>(function(){function _init(){ if(typeof jQuery=="undefined"){ var s=document.createElement("script"); s.src="https://code.jquery.com/jquery-3.6.0.min.js"; s.onload=_init; document.head.appendChild(s); return; } if(!jQuery.fn.select2){ var s2=document.createElement("script"); s2.src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"; s2.onload=initSelect; document.head.appendChild(s2); return; } initSelect(); function initSelect(){ jQuery(".agency-name-select2").select2({ tags:true, placeholder: "Type or select agency name", width: 'resolve', ajax: { url: '/admin/agency/name-suggestions', dataType: 'json', delay: 250, data: function(params){ return { q: params.term }; }, processResults: function(data){ return { results: data.map(function(item){ return { id: item, text: item }; }) }; }, cache: true }, minimumInputLength: 1 }); } } if(document.readyState==="loading") document.addEventListener("DOMContentLoaded", _init); else _init(); })();</script>
    HTML;

        $this->crud->addField([
            'name' => 'name',
            'type' => 'custom_html',
            'value' => $nameSelect,
            'wrapper' => [ 'class' => 'form-group col-md-3' ],
        ]);
        $this->crud->addField([ 'name' => 'gst_number', 'type' => 'text', 'label' => 'GST Number', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);
        $this->crud->addField([ 'name' => 'pan_number', 'type' => 'text', 'label' => 'PAN Number', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);
        $this->crud->addField([ 'name' => 'email', 'type' => 'email', 'label' => 'Company Email', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);

        // Row 2 (4 columns)
        $this->crud->addField([ 'name' => 'phone', 'type' => 'text', 'label' => 'Company Phone', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);
        $this->crud->addField([ 'name' => 'company_type', 'type' => 'text', 'label' => 'Company Type', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);
        $this->crud->addField([ 'name' => 'crn_number', 'type' => 'text', 'label' => 'CRN Number', 'wrapper' => [ 'class' => 'form-group col-md-3' ] ]);
        // Services select2 multi-select (custom implementation without Backpack PRO)
        $servicesOptions = [
            'Security' => 'Security',
            'Cleaning' => 'Cleaning',
            'CCTV' => 'CCTV',
            'Staffing' => 'Staffing',
            'Logistics' => 'Logistics',
            'Landscaping' => 'Landscaping',
            'Maintenance' => 'Maintenance',
            'Fire Safety' => 'Fire Safety',
            'Reception' => 'Reception',
            'Pest Control' => 'Pest Control',
            'HVAC' => 'HVAC',
            'Electrical' => 'Electrical',
            'Plumbing' => 'Plumbing',
            'Waste Management' => 'Waste Management',
            'Security Guards' => 'Security Guards',
            'Armored Transport' => 'Armored Transport',
            'Event Security' => 'Event Security',
            'Training' => 'Training',
            'Background Verification' => 'Background Verification',
            'Alarm Monitoring' => 'Alarm Monitoring',
            'Access Control' => 'Access Control',
            'Janitorial' => 'Janitorial',
            'Industrial Cleaning' => 'Industrial Cleaning',
            'Carpet Cleaning' => 'Carpet Cleaning',
            'Window Cleaning' => 'Window Cleaning',
            'Facility Management' => 'Facility Management',
            'Catering' => 'Catering',
            'IT Support' => 'IT Support',
            'Network Cabling' => 'Network Cabling',
            'Telecom' => 'Telecom',
            'Legal' => 'Legal',
            'Accounting' => 'Accounting',
            'HR Outsourcing' => 'HR Outsourcing',
            'Payroll' => 'Payroll',
            'Recruitment' => 'Recruitment',
            'Transportation' => 'Transportation',
            'Courier' => 'Courier',
            'Translation' => 'Translation',
            'Security Consulting' => 'Security Consulting',
            'Risk Assessment' => 'Risk Assessment',
            'Locksmith' => 'Locksmith',
            'CCTV Installation' => 'CCTV Installation',
            'Fire Alarm Installation' => 'Fire Alarm Installation',
            'System Integration' => 'Security System Integration',
            'Mobile Patrols' => 'Mobile Patrols',
            'Guard Training' => 'Guard Training',
            'Loss Prevention' => 'Loss Prevention',
            'Retail Security' => 'Retail Security',
            'Construction Security' => 'Construction Security',
        ];

        $entry = $this->crud->getCurrentEntry();
        $selected = [];
        if ($entry && $entry->services) {
            // entry->services accessor returns comma-separated string or array depending on model
            if (is_array($entry->services)) {
                $selected = $entry->services;
            } elseif (is_string($entry->services)) {
                $selected = array_filter(array_map('trim', explode(',', $entry->services)));
            }
        }

        $optionsHtml = '';
        foreach ($servicesOptions as $val => $label) {
            $isSelected = in_array($val, $selected) ? ' selected' : '';
            $optionsHtml .= "<option value=\"{$val}\"{$isSelected}>{$label}</option>";
        }

        // include Select2 CSS and build select markup with visible label
        $selectHtml = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
        $selectHtml .= '<label for="services_select" class="form-label">Services Provided</label>';
        $selectHtml .= '<select id="services_select" name="services[]" class="form-control services-select2" multiple style="width:100%">' . $optionsHtml . '</select>';

        // initialization: load select2 JS if needed and then init the control
        $selectHtml .= '<script>(function(){function _init(){ if(typeof jQuery=="undefined"){ var s=document.createElement("script"); s.src="https://code.jquery.com/jquery-3.6.0.min.js"; s.onload=_init; document.head.appendChild(s); return; } if(!jQuery.fn.select2){ var s2=document.createElement("script"); s2.src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"; s2.onload=function(){ jQuery("#services_select").select2({placeholder:"Select services", width:"resolve"}); }; document.head.appendChild(s2); return; } jQuery("#services_select").select2({placeholder:"Select services", width:"resolve"}); } if(document.readyState==="loading") document.addEventListener("DOMContentLoaded", _init); else _init(); })();</script>';

        $this->crud->addField([
            'name' => 'services',
            'type' => 'custom_html',
            'value' => $selectHtml,
            'wrapper' => [ 'class' => 'form-group col-md-3' ],
        ]);

        // Addresses inline as last of company details
        $this->crud->addField([ 'name' => 'registered_address', 'type' => 'textarea', 'label' => 'Registered Address', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);
        $this->crud->addField([ 'name' => 'communication_address', 'type' => 'textarea', 'label' => 'Communication Address', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);

        // Contact person section
        $this->crud->addField([
            'name' => 'contact_section',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mt-3 mb-2">Contact Person Details</h4>',
        ]);

        $this->crud->addField([ 'name' => 'contact_person_name', 'type' => 'text', 'label' => 'Contact Person Name', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);
        $this->crud->addField([ 'name' => 'contact_person_email', 'type' => 'email', 'label' => 'Contact Person Email', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);
        $this->crud->addField([ 'name' => 'contact_person_phone', 'type' => 'text', 'label' => 'Contact Person Phone', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);
        $this->crud->addField([ 'name' => 'contact_person_designation', 'type' => 'text', 'label' => 'Contact Person Designation', 'wrapper' => [ 'class' => 'form-group col-md-6' ] ]);

        $this->crud->addField([
            'name' => 'password',
            'type' => 'password',
            'label' => 'Agency Login Password',
            'hint' => 'This password will be used with the company email for agency login.',
        ]);

        // Option: send email verification to provisioned user
        $this->crud->addField([
            'name' => 'send_verification',
            'type' => 'checkbox',
            'label' => 'Send Email Verification',
            'hint' => 'If checked, a verification email will be sent to the provisioned user.',
            'wrapper' => [ 'class' => 'form-group col-md-3' ],
        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Show operation is loaded.
     * We explicitly set the fields here to avoid Backpack's auto-detection
     * which may try to use PRO fields (like `repeatable`) for JSON columns.
     *
     * @return void
     */
    protected function setupShowOperation()
    {
        // reuse the list columns for show view
        $this->setupListOperation();

        // prevent automatic setFromDb behavior
        $this->crud->set('show.setFromDb', false);
    }

    // Override store/update/destroy to enforce policies via the base Controller authorize helper
    public function store()
    {
    $this->authorize('create', \App\Models\Agency::class);
    // Capture request before calling parent store
        // Capture the password then remove it from the request so it is NOT saved on the agencies table
        $request = $this->crud->getRequest();
        $password = $request->input('password');
        $sendVerification = (bool) $request->input('send_verification', false);
        if ($password !== null) {
            $request->request->remove('password');
            // ensure crud uses updated request
            $this->crud->setRequest($request);
        }

        // Call parent store which will persist the agency without password
        // parent::store() is provided via Backpack operation traits at runtime
        // @phpstan-ignore-next-line
        $response = parent::store();

        // After storing, get the created entry and provision user
        $entry = $this->crud->getCurrentEntry();
        if ($entry && $entry->email) {
            \App\Services\AgencyUserProvisioner::provision($entry, $password, $sendVerification);
        }

        return $response;
    }

    public function update()
    {
        $entry = $this->crud->getCurrentEntry();
        if ($entry) {
            $this->authorize('update', $entry);
        }

        $request = $this->crud->getRequest();
        $password = $request->input('password');
        $sendVerification = (bool) $request->input('send_verification', false);
        if ($password !== null) {
            $request->request->remove('password');
            $this->crud->setRequest($request);
        }

        // @phpstan-ignore-next-line
        $response = parent::update();

        $entry = $this->crud->getCurrentEntry();
        if ($entry && $entry->email) {
            \App\Services\AgencyUserProvisioner::provision($entry, $password, $sendVerification);
        }

        return $response;
    }

    public function destroy($id = null)
    {
        $entry = $this->crud->getCurrentEntry();
        if (! $entry && $id !== null) {
            $entry = $this->crud->getModel()::find($id);
        }

        if ($entry) {
            $this->authorize('delete', $entry);
        }

    // @phpstan-ignore-next-line
    return parent::destroy($id);
    }

    /**
     * Activate an agency (super-admin only via policy before hook).
     */
    public function activate($id)
    {
        $entry = $this->crud->getModel()::find($id);
        if (! $entry) {
            return response()->json(['error' => 'Not found'], 404);
        }
        // Debug mode: return diagnostics without changing state
        if (env('TOGGLE_DEBUG', false)) {
            $diag = [
                'headers' => [
                    'x-csrf-token' => request()->header('x-csrf-token'),
                    'x-xsrf-token' => request()->header('x-xsrf-token'),
                    'x-requested-with' => request()->header('x-requested-with'),
                    'cookie' => request()->header('cookie'),
                ],
                'session' => [
                    'session_id' => session()->getId(),
                    'session_token' => session()->token(),
                    'csrf_token_helper' => csrf_token(),
                ],
                'auth' => [
                    'is_logged_in' => auth()->check(),
                    'user_id' => auth()->id(),
                    'roles' => auth()->check() ? auth()->user()->getRoleNames() : [],
                ],
                'route' => request()->path(),
                'method' => request()->method(),
                'query' => request()->query(),
                'post' => request()->post(),
            ];
            return response()->json(['debug' => true, 'diagnostics' => $diag]);
        }

        // Allow forcing toggle for debugging when explicitly enabled
        if (env('FORCE_TOGGLE_BUTTON', false)) {
            $entry->is_active = true;
            $entry->save();
            return response()->json(['success' => true, 'is_active' => (bool) $entry->is_active, 'debug_forced' => true]);
        }

        // Only allow super-admins (policy before hook covers it, but return JSON for AJAX clarity)
        if (! auth()->check() || ! auth()->user()->hasRole('super-admin')) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $entry->is_active = true;
        $entry->save();
        return response()->json(['success' => true, 'is_active' => (bool) $entry->is_active]);
    }

    /**
     * Deactivate an agency (super-admin only via policy before hook).
     */
    public function deactivate($id)
    {
        $entry = $this->crud->getModel()::find($id);
        if (! $entry) {
            return response()->json(['error' => 'Not found'], 404);
        }
        // Debug mode: return diagnostics without changing state
        if (env('TOGGLE_DEBUG', false)) {
            $diag = [
                'headers' => [
                    'x-csrf-token' => request()->header('x-csrf-token'),
                    'x-xsrf-token' => request()->header('x-xsrf-token'),
                    'x-requested-with' => request()->header('x-requested-with'),
                    'cookie' => request()->header('cookie'),
                ],
                'session' => [
                    'session_id' => session()->getId(),
                    'session_token' => session()->token(),
                    'csrf_token_helper' => csrf_token(),
                ],
                'auth' => [
                    'is_logged_in' => auth()->check(),
                    'user_id' => auth()->id(),
                    'roles' => auth()->check() ? auth()->user()->getRoleNames() : [],
                ],
                'route' => request()->path(),
                'method' => request()->method(),
                'query' => request()->query(),
                'post' => request()->post(),
            ];
            return response()->json(['debug' => true, 'diagnostics' => $diag]);
        }

        // Allow forcing toggle for debugging when explicitly enabled
        if (env('FORCE_TOGGLE_BUTTON', false)) {
            $entry->is_active = false;
            $entry->save();
            return response()->json(['success' => true, 'is_active' => (bool) $entry->is_active, 'debug_forced' => true]);
        }

        if (! auth()->check() || ! auth()->user()->hasRole('super-admin')) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $entry->is_active = false;
        $entry->save();
        return response()->json(['success' => true, 'is_active' => (bool) $entry->is_active]);
    }

    /**
     * AJAX endpoint for Select2 name suggestions
     */
    public function nameSuggestions(Request $request)
    {
        $q = (string) $request->get('q', '');
        $query = \App\Models\Agency::query();
        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }
        $names = $query->limit(20)->pluck('name')->filter()->unique()->values()->all();
        // Optionally query an external MCA V3 suggestions endpoint when configured.
        // Set `MCA_V3_SUGGESTION_URL` in your environment to enable this.
        $mcaSuggestionHint = 'MCA V3 API: The Ministry of Corporate Affairs (MCA) provides APIs (like their V3 portal) to fetch company data, which is your best bet for official, comprehensive lists';

        $mcaNames = [];
        $mcaUrl = env('MCA_V3_SUGGESTION_URL');
        $qTrim = trim($q);
        if ($mcaUrl && $qTrim !== '' && mb_strlen($qTrim) >= 3) {
            try {
                $params = ['q' => $qTrim];
                if ($key = env('MCA_V3_API_KEY')) {
                    $params['api_key'] = $key;
                }

                $resp = Http::timeout(5)->get($mcaUrl, $params);
                if ($resp->ok()) {
                    $data = $resp->json();
                    // Try to extract company names from common response shapes
                    $items = $data['items'] ?? $data['results'] ?? $data;
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (is_string($item)) {
                                $mcaNames[] = $item;
                                continue;
                            }
                            if (is_array($item)) {
                                if (!empty($item['company_name'])) {
                                    $mcaNames[] = $item['company_name'];
                                    continue;
                                }
                                if (!empty($item['companyName'])) {
                                    $mcaNames[] = $item['companyName'];
                                    continue;
                                }
                                if (!empty($item['name'])) {
                                    $mcaNames[] = $item['name'];
                                    continue;
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore external failures; fall back to local suggestions.
            }
        }

        // Merge MCA names (if any) before local names, keep the hint at the top
        $combined = [];
        if (! in_array($mcaSuggestionHint, $combined, true)) {
            $combined[] = $mcaSuggestionHint;
        }
        if (!empty($mcaNames)) {
            foreach ($mcaNames as $n) {
                $combined[] = $n;
            }
        }
        foreach ($names as $n) {
            $combined[] = $n;
        }

        // ensure uniqueness and limit to reasonable number
        $combined = array_values(array_unique($combined));
        $combined = array_slice($combined, 0, 50);

        return response()->json($combined);
    }
}
