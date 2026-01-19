@extends(backpack_view('blank'))

@section('header')
  <section class="container-fluid mb-2">
    <h1 class="text-capitalize">Agencies</h1>
  </section>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    @if(empty($agencies) || count($agencies) === 0)
      <div class="alert alert-info">No agencies found.</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Agency</th>
              <th>Tenant</th>
              <th>Contact</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($agencies as $i => $a)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $a['name'] }}</td>
              <td>{{ $a['tenant_name'] }}</td>
              <td>
                {{ $a['contact_name'] }}<br/>
                <small>{{ $a['contact_email'] }} | {{ $a['contact_phone'] }}</small>
              </td>
              <td>{{ ucfirst($a['status']) }}</td>
              <td>{{ \Carbon\Carbon::parse($a['created_at'])->diffForHumans() }}</td>
              <td>
                <a href="{{ backpack_url('agency-leads/'.$a['tenant_uuid'].'/'.$a['agency_id'].'/followups') }}" class="btn btn-sm btn-primary">Followups</a>
                <a href="{{ backpack_url('agency-leads/'.$a['tenant_uuid'].'/'.$a['agency_id']) }}" class="btn btn-sm btn-secondary">View Details</a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
@extends(backpack_view('blank'))

@section('header')
  <section class="container-fluid mb-2">
    <h1 class="text-capitalize">Agency Leads — {{ ucfirst($status) }}</h1>
  </section>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    @if(empty($agencies))
      <div class="alert alert-info">No agencies found for this filter.</div>
    @else
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Agency</th>
              <th>Tenant</th>
              <th>Contact</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($agencies as $i => $a)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $a['name'] }}</td>
              <td>{{ $a['tenant_name'] }} ({{ $a['tenant_uuid'] }})</td>
              <td>
                @if($a['contact_name'])<div>{{ $a['contact_name'] }}</div>@endif
                @if($a['contact_email'])<div><a href="mailto:{{ $a['contact_email'] }}">{{ $a['contact_email'] }}</a></div>@endif
                @if($a['contact_phone'])<div>{{ $a['contact_phone'] }}</div>@endif
              </td>
              <td>{{ ucfirst($a['status']) }}</td>
              <td>{{ \\Carbon\\Carbon::parse($a['created_at'])->diffForHumans() }}</td>
              <td>
                <a href="{{ backpack_url('agency-leads/'.$a['tenant_uuid'].'/'.$a['agency_id'].'/followups') }}" class="btn btn-sm btn-outline-primary">Followups</a>
                <a href="{{ backpack_url('agency-leads/'.$a['tenant_uuid'].'/'.$a['agency_id']) }}" class="btn btn-sm btn-primary">View Details</a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
