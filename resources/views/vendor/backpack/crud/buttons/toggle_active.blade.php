@php
    // $entry is provided by Backpack when rendering the button view
    // Render only for super-admins, unless FORCE_TOGGLE_BUTTON is set to true in .env (debug helper)
    $forceShow = env('FORCE_TOGGLE_BUTTON', false);
    if (! $forceShow && (! auth()->check() || ! auth()->user()->hasRole('super-admin'))) {
        // don't render button for non-super-admins
        return;
    }
    $id = $entry->getKey();
    $isActive = (bool) ($entry->is_active ?? true);
    $label = $isActive ? 'Deactivate' : 'Activate';
    $btnClass = $isActive ? 'btn btn-sm btn-danger' : 'btn btn-sm btn-success';
    $activateUrl = url("/admin/agency/{$id}/activate");
    $deactivateUrl = url("/admin/agency/{$id}/deactivate");
    $actionUrl = $isActive ? $deactivateUrl : $activateUrl;
@endphp

<a href="#" class="{{ $btnClass }} toggle-agency-active" data-url="{{ $actionUrl }}" data-id="{{ $id }}">{{ $label }}</a>

<script>
(function(){
    // register handler only once to avoid duplicate confirmation popups
    if (window._toggleAgencyActiveInitialized) return; window._toggleAgencyActiveInitialized = true;
    function findBooleanCell(row){
        // Try multiple selectors to find the is_active column cell
        var cell = row.querySelector('td.column-is_active');
        if(cell) return cell;
        cell = row.querySelector('td[data-column-name="is_active"]');
        if(cell) return cell;
        // fallback: find any td that contains the text 'true'/'false' or a checkbox
        var tds = row.querySelectorAll('td');
        for(var i=0;i<tds.length;i++){
            var txt = tds[i].textContent || '';
            if(/true|false|Yes|No|Active|Inactive/i.test(txt)) return tds[i];
        }
        return null;
    }

    document.addEventListener('click', function(e){
        var btn = e.target.closest('.toggle-agency-active');
        if(!btn) return;
        e.preventDefault();
        if(!confirm('Are you sure?')) return;
        var url = btn.dataset.url;
        var id = btn.dataset.id;
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''),
                'Accept': 'application/json',
            }
        }).then(function(resp){
            if(!resp.ok){
                return resp.text().then(function(text){
                    throw new Error('HTTP ' + resp.status + ' - ' + text);
                });
            }
            return resp.json();
        }).then(function(data){
            if(data && typeof data.is_active !== 'undefined'){
                var tr = btn.closest('tr[data-entry-id]');
                if(!tr) tr = btn.closest('tr');
                var cell = findBooleanCell(tr);
                if(cell){
                    cell.textContent = data.is_active ? 'Yes' : 'No';
                }
                // update button label and class, and data-url
                if(data.is_active){
                    btn.textContent = 'Deactivate';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-danger');
                    btn.dataset.url = '/admin/agency/' + id + '/deactivate';
                } else {
                    btn.textContent = 'Activate';
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-success');
                    btn.dataset.url = '/admin/agency/' + id + '/activate';
                }
            } else {
                alert('Unexpected response');
            }
        }).catch(function(err){
            console.error('Toggle request failed:', err);
            alert('Request failed: ' + (err && err.message ? err.message : 'unknown error'));
        });
    });
})();
</script>
