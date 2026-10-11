@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
@endphp
<div class="overflow-hidden overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Order</th>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">Sales Rep</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3">Created</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($orders as $order)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-900">#{{ $order->id }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $order->customer->company_name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $order->salesRepresentative?->name ?? '—' }}</td>
                    <td class="px-4 py-3"><x-order-status :status="$order->status" /></td>
                    <td class="px-4 py-3 text-right font-medium text-slate-900">
                        ${{ number_format((float) $order->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('orders.show', $order) }}" class="font-medium text-slate-700 hover:text-slate-900">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
