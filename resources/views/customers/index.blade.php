@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Customers</h1>
        <p class="mt-1 text-sm text-slate-500">Wholesale accounts and live credit positions.</p>
    </div>

    <div class="overflow-hidden overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Company</th>
                    <th class="px-6 py-3">Contact</th>
                    <th class="px-6 py-3 text-right">Credit limit</th>
                    <th class="px-6 py-3 text-right">Outstanding</th>
                    <th class="px-6 py-3 text-right">Available</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $customer)
                    @php $available = (float) $customer->credit_limit - (float) $customer->outstanding_balance; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $customer->company_name }}</td>
                        <td class="px-6 py-3 text-slate-500">
                            <div>{{ $customer->email ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $customer->phone ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-3 text-right text-slate-700">${{ number_format((float) $customer->credit_limit, 2) }}</td>
                        <td class="px-6 py-3 text-right text-slate-700">${{ number_format((float) $customer->outstanding_balance, 2) }}</td>
                        <td class="px-6 py-3 text-right font-medium {{ $available > 0 ? 'text-green-700' : 'text-red-700' }}">
                            ${{ number_format($available, 2) }}
                        </td>
                        <td class="px-6 py-3"><x-customer-status :status="$customer->status" /></td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('customers.show', $customer) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endsection
