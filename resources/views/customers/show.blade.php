@extends('layouts.app')

@section('title', $customer->company_name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Customers</a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $customer->company_name }}</h1>
            <x-customer-status :status="$customer->status" />
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ $customer->email ?? '—' }} · {{ $customer->phone ?? '—' }}</p>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Credit limit</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format((float) $customer->credit_limit, 2) }}</div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Outstanding balance</div>
            <div class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format((float) $customer->outstanding_balance, 2) }}</div>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Available credit</div>
            <div class="mt-1 text-2xl font-semibold {{ $availableCredit > 0 ? 'text-green-700' : 'text-red-700' }}">
                ${{ number_format($availableCredit, 2) }}
            </div>
        </div>
    </div>

    <h2 class="mb-3 text-lg font-semibold text-slate-900">Recent Orders</h2>
    @include('orders._table', ['orders' => $orders])
@endsection
