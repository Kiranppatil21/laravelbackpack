@extends(backpack_view('blank'))

@section('header')
  <section class="container-fluid mb-2">
    <h1 class="text-capitalize">Agency Details</h1>
  </section>
@endsection

@section('content')
<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Total Active Clients</h5>
        <p class="h3">{{ $details['total_active_clients'] ?? 0 }}</p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Total Inactive Clients</h5>
        <p class="h3">{{ $details['total_inactive_clients'] ?? 0 }}</p>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5>Recent Followups</h5>
        @if(empty($details['recent_followups']) || count($details['recent_followups']) === 0)
          <div class="text-muted">No recent followups available.</div>
        @else
          <ul class="list-group">
            @foreach($details['recent_followups'] as $f)
            <li class="list-group-item">
              <div><strong>{{ $f->lead_person_id ? ($f->leadPerson->name ?? 'Lead') : 'Lead' }}</strong></div>
              <div class="text-muted">{{ ucfirst($f->communication_type ?? 'n/a') }} · {{ $f->followed_up_at ? $f->followed_up_at->diffForHumans() : $f->created_at->diffForHumans() }}</div>
              <div class="mt-2">{{ $f->notes }}</div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
