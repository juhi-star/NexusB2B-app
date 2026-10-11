@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@php
    $isDraft = $order->status === \App\Enums\OrderStatus::Draft;
    $isSubmitted = $order->status === \App\Enums\OrderStatus::Submitted;
    $isApproved = $order->status === \App\Enums\OrderStatus::Approved;
    $isRejected = $order->status === \App\Enums\OrderStatus::Rejected;
    $isDispatched = $order->status === \App\Enums\OrderStatus::Dispatched;
@endphp

@section('content')
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Orders</a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">Order #{{ $order->id }}</h1>
            <x-order-status :status="$order->status" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Line Items</h2>
                    <span class="text-sm text-slate-500">{{ $order->items->count() }} item(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-right">Qty</th>
                                <th class="px-6 py-3 text-right">Unit</th>
                                <th class="px-6 py-3 text-right">Discount</th>
                                <th class="px-6 py-3 text-right">Line total</th>
                                @if ($isDraft)<th class="px-6 py-3"></th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($order->items as $item)
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="font-medium text-slate-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-right text-slate-700">{{ $item->quantity }}</td>
                                    <td class="px-6 py-3 text-right text-slate-700">${{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-6 py-3 text-right text-slate-500">{{ number_format((float) $item->discount_percent, 0) }}%</td>
                                    <td class="px-6 py-3 text-right font-medium text-slate-900">${{ number_format((float) $item->line_total, 2) }}</td>
                                    @if ($isDraft)
                                        <td class="px-6 py-3">
                                            <div class="flex items-center justify-end gap-3">
                                                <form method="POST" action="{{ route('orders.items.update', [$order, $item->product]) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                                           class="w-16 rounded-md border border-slate-300 px-2 py-1 text-sm">
                                                    <button type="submit" class="text-sm font-medium text-slate-700 hover:text-slate-900">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('orders.items.destroy', [$order, $item->product]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400">No line items yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200">
                                <td colspan="{{ $isDraft ? 4 : 3 }}" class="px-6 py-3 text-right font-medium text-slate-500">Order total</td>
                                <td class="px-6 py-3 text-right text-base font-semibold text-slate-900">${{ number_format((float) $order->total_amount, 2) }}</td>
                                @if ($isDraft)<td></td>@endif
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($isDraft && $products->isNotEmpty())
                    <div class="border-t border-slate-100 px-6 py-4">
                        <form method="POST" action="{{ route('orders.items.store', $order) }}" class="flex flex-wrap items-center gap-3">
                            @csrf
                            <select name="product_id" required
                                    class="min-w-[14rem] flex-1 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                                <option value="">Add a product…</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} — ${{ number_format((float) $product->base_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="quantity" value="1" min="1"
                                   class="w-24 rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <button type="submit"
                                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                                Add item
                            </button>
                        </form>
                        @error('product_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('quantity')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-4 font-semibold text-slate-900">Activity</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Created</dt>
                        <dd class="text-slate-700">{{ $order->created_at->format('M j, Y g:ia') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Sales rep</dt>
                        <dd class="text-slate-700">{{ $order->salesRepresentative?->name ?? '—' }}</dd>
                    </div>
                    @if ($order->approved_at)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Approved</dt>
                            <dd class="text-slate-700">{{ $order->approvedBy?->name }} · {{ $order->approved_at->format('M j, Y g:ia') }}</dd>
                        </div>
                    @endif
                    @if ($order->rejected_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Rejected</dt>
                            <dd class="text-right text-slate-700">{{ $order->rejectedBy?->name }} · {{ $order->rejected_at->format('M j, Y g:ia') }}</dd>
                        </div>
                    @endif
                    @if ($order->dispatched_at)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Dispatched</dt>
                            <dd class="text-slate-700">{{ $order->dispatched_at->format('M j, Y g:ia') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-4 font-semibold text-slate-900">Customer</h2>
                <a href="{{ route('customers.show', $order->customer) }}" class="text-base font-medium text-slate-900 hover:underline">
                    {{ $order->customer->company_name }}
                </a>
                <div class="mt-1 text-sm text-slate-500">
                    <div>{{ $order->customer->email ?? '—' }}</div>
                    <div>{{ $order->customer->phone ?? '—' }}</div>
                </div>

                <dl class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Credit limit</dt>
                        <dd class="font-medium text-slate-900">${{ number_format((float) $order->customer->credit_limit, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Outstanding</dt>
                        <dd class="font-medium text-slate-900">${{ number_format((float) $order->customer->outstanding_balance, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Available</dt>
                        <dd class="font-semibold {{ $availableCredit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            ${{ number_format($availableCredit, 2) }}
                        </dd>
                    </div>
                </dl>

                @if ($isDraft && (float) $order->total_amount > $availableCredit)
                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        This order exceeds the customer's available credit. It cannot be submitted until the order is
                        reduced or the balance is settled.
                    </div>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-4 font-semibold text-slate-900">Actions</h2>

                @if ($isDraft)
                    @can('submit', $order)
                        <form method="POST" action="{{ route('orders.submit', $order) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                                Submit for credit review
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-slate-400">Submitting locks the order for credit review by a manager.</p>
                    @else
                        <p class="text-sm text-slate-500">This draft cannot be submitted by your account.</p>
                    @endcan
                @elseif ($isSubmitted)
                    @if (auth()->user()->isCreditManager())
                        <form method="POST" action="{{ route('orders.review', $order) }}" class="space-y-3">
                            @csrf
                            <button type="submit" name="decision" value="approve"
                                    class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                Approve order
                            </button>
                            <textarea name="remarks" rows="3" placeholder="Rejection reason (required to reject)"
                                      class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="submit" name="decision" value="reject"
                                    class="w-full rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                Reject order
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500">Awaiting credit review by a manager.</p>
                    @endif
                @elseif ($isApproved)
                    @can('dispatch', $order)
                        <form method="POST" action="{{ route('orders.dispatch', $order) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Mark as dispatched
                            </button>
                        </form>
                    @endcan
                    <p class="mt-2 text-xs text-slate-400">Approved and awaiting dispatch.</p>
                @elseif ($isRejected)
                    <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <p class="font-medium">Rejected</p>
                        <p class="mt-1">{{ $order->rejection_reason }}</p>
                    </div>
                @elseif ($isDispatched)
                    <div class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm text-indigo-800">
                        Dispatched {{ $order->dispatched_at?->format('M j, Y g:ia') }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
