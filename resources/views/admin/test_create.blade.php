@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>Test Create Page</h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Test</h3>
                </div>
                <div class="card-body">
                    <p>This is a test create page to verify routing and authentication.</p>
                    
                    <h4>User Info:</h4>
                    @if(backpack_user())
                        <ul>
                            <li><strong>Name:</strong> {{ backpack_user()->name }}</li>
                            <li><strong>Email:</strong> {{ backpack_user()->email }}</li>
                            @if(method_exists(backpack_user(), 'getRoleNames'))
                                <li><strong>Roles:</strong> {{ backpack_user()->getRoleNames()->implode(', ') }}</li>
                            @endif
                        </ul>
                    @else
                        <p>No user authenticated</p>
                    @endif

                    <h4>Attendance Create Link:</h4>
                    <a href="{{ backpack_url('attendance/create') }}" class="btn btn-primary">Go to Attendance Create</a>
                </div>
            </div>
        </div>
    </div>
@endsection