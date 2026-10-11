@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Orders</h1>
        @can('create', App\Models\Order::class)
            <a href="{{ route('orders.create') }}"
               class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                New order
            </a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex items-center gap-2">
        <select name="status"
                class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Filter
        </button>
        @if (request('status'))
            <a href="{{ route('orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
        @endif
    </form>

    @include('orders._table', ['orders' => $orders])

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection
