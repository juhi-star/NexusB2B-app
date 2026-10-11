@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @if (auth()->user()->isCreditManager())
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Credit Review Queue</h1>
            <p class="mt-1 text-sm text-slate-500">Orders awaiting a credit decision.</p>
        </div>

        @if ($pendingOrders->isEmpty())
            <div class="mb-8 rounded-lg border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-400">
                No orders are awaiting review.
            </div>
        @else
            <div class="mb-8">
                @include('orders._table', ['orders' => $pendingOrders])
            </div>
        @endif

        <h2 class="mb-3 text-lg font-semibold text-slate-900">Recent Orders</h2>
        @include('orders._table', ['orders' => $recentOrders])
    @else
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">My Orders</h1>
                <p class="mt-1 text-sm text-slate-500">Your most recent draft and submitted orders.</p>
            </div>
            @can('create', App\Models\Order::class)
                <a href="{{ route('orders.create') }}"
                   class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    New order
                </a>
            @endcan
        </div>

        @if ($orders->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-400">
                You have not created any orders yet.
                @can('create', App\Models\Order::class)
                    <a href="{{ route('orders.create') }}" class="font-medium text-slate-700 underline">Create your first order</a>.
                @endcan
            </div>
        @else
            @include('orders._table', ['orders' => $orders])
        @endif
    @endif
@endsection
