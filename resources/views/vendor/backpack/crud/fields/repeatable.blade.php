@php
    // Fallback view for 'repeatable' field when Backpack PRO is not installed.
    // It renders a read-only JSON preview and includes a hidden input so form submissions still include the value.
    $fieldName = $field['name'] ?? null;
    $label = $field['label'] ?? (is_string($fieldName) ? ucwords(str_replace('_', ' ', $fieldName)) : 'Repeatable');
    $value = $field['value'] ?? old($fieldName) ?? (isset($crud) && $crud->getCurrentEntry() ? data_get($crud->getCurrentEntry(), $fieldName) : null);
    $preview = is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($preview === null) {
        $preview = '';
    }
@endphp

<div class="form-group col-md-12">
    <label>{{ $label }}</label>
    <pre style="white-space:pre-wrap; background:#f8f9fa; padding:8px; border:1px solid #e1e1e1;">{{ $preview }}</pre>
    @if($fieldName)
        <input type="hidden" name="{{ $fieldName }}" value='{{ is_string($value) ? e($value) : e(json_encode($value)) }}'>
    @endif
</div>
