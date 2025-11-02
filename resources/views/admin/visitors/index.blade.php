@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Visitor logs (plain view)</h1>

        <form method="get" class="mb-4 flex gap-2">
            <input name="search" value="{{ request('search') }}" placeholder="Search visitor" class="input" />
            <input type="date" name="from" value="{{ request('from') }}" class="input" />
            <input type="date" name="to" value="{{ request('to') }}" class="input" />
            <button class="btn btn-primary">Filter</button>
        </form>

        <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead>
                <tr>
                    <th class="px-6 py-2 text-left">Visitor</th>
                    <th class="px-6 py-2">Host</th>
                    <th class="px-6 py-2">Check in</th>
                    <th class="px-6 py-2">Check out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-2">{{ $log->visitor ? $log->visitor->name : '—' }}</td>
                        <td class="px-6 py-2">{{ $log->host ? $log->host->name : ($log->host_id ?? '—') }}</td>
                        <td class="px-6 py-2">{{ optional($log->check_in_at)->toDateTimeString() ?? '—' }}</td>
                        <td class="px-6 py-2">{{ optional($log->check_out_at)->toDateTimeString() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $logs->links() }}
    </div>
@endsection
