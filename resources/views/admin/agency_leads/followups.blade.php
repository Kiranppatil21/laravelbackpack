@extends(backpack_view('blank'))

@section('header')
  <section class="container-fluid mb-2">
    <h1 class="text-capitalize">Agency Followups</h1>
  </section>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-3">
      <div class="col-md-6">
        <h5>Add Followup</h5>
        <form method="POST" action="{{ route('admin.agency.leads.followups.store', ['tenantUuid' => $tenantUuid, 'agencyId' => $agencyId]) }}" enctype="multipart/form-data">
          @csrf
          <div class="mb-2">
            <label class="form-label">Lead Person</label>
            <select name="lead_person_id" class="form-control">
              <option value="">-- Select --</option>
              @if(!empty($leadPersons))
                @foreach($leadPersons as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Communication Type</label>
            <select name="communication_type" class="form-control" required>
              <option value="">-- Select --</option>
              <option value="call">Call</option>
              <option value="email">Email</option>
              <option value="meeting">Meeting</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Attachments</label>
            <input type="file" name="attachments[]" class="form-control" multiple />
          </div>
          <div>
            <button class="btn btn-primary btn-sm">Save Followup</button>
          </div>
        </form>
      </div>

      <div class="col-md-6">
        <h5>Followup History</h5>
        @if(empty($followups) || count($followups) === 0)
          <div class="alert alert-info">No followups found or tenant does not have followups table. Run tenant migrations to enable followups.</div>
        @else
          <ul class="list-group">
            @foreach($followups as $f)
            <li class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <strong>{{ $f->leadPerson->name ?? 'Unknown' }}</strong>
                  <div class="text-muted">{{ ucfirst($f->communication_type ?? 'n/a') }} · {{ $f->followed_up_at ? $f->followed_up_at->diffForHumans() : $f->created_at->diffForHumans() }}</div>
                  <div class="mt-2">{{ $f->notes }}</div>
                </div>
                <div>
                  @if(!empty($f->attachments))
                    <a href="#" class="btn btn-sm btn-outline-secondary">Download</a>
                  @endif
                </div>
              </div>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
    
    @endif
  </div>
</div>
@endsection
