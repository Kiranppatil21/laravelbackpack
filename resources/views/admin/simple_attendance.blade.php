@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">Simple Attendance</span>
            <small>No CRUD, just a basic list</small>
        </h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attendance Records</h3>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->id }}</td>
                                    <td>{{ $attendance->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $attendances->links() }}
                    @else
                        <p>No attendance records found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection