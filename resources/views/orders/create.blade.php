@extends('layouts.app')

@section('title', 'New Order')

@section('content')
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Orders</a>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">New Draft Order</h1>
    </div>

    <form method="POST" action="{{ route('orders.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <label for="customer_id" class="block text-sm font-medium text-slate-700">Customer</label>
            <select name="customer_id" id="customer_id" required
                    class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <option value="">Select a customer…</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>
                        {{ $customer->company_name }} — ${{ number_format((float) $customer->credit_limit, 2) }} credit limit
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Line Items</h2>
                <button type="button" id="add-row"
                        class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Add item
                </button>
            </div>

            @error('items')
                <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div id="items" class="space-y-3"></div>

            <div class="mt-4 border-t border-slate-100 pt-4 text-right text-sm text-slate-500">
                Estimated total:
                <span id="grand-total" class="ml-1 font-semibold text-slate-900">$0.00</span>
                <p class="mt-1 text-xs text-slate-400">
                    Volume discounts (5% at 50+ units, 10% at 100+) are applied when the order is saved.
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('orders.index') }}"
               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Create draft order
            </button>
        </div>
    </form>

    <template id="item-template">
        <div class="item-row flex items-center gap-3">
            <select name="items[__INDEX__][product_id]" required
                    class="product-select flex-1 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <option value="">Select a product…</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->base_price }}">
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
            <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" required
                   class="quantity w-24 rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            <span class="line-price w-24 text-right text-sm text-slate-500">$0.00</span>
            <button type="button" class="remove-row text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const prices = @json($products->pluck('base_price', 'id'));
            const oldItems = @json(old('items', []));
            const container = document.getElementById('items');
            const template = document.getElementById('item-template');
            let index = 0;

            const discountFor = (qty) => (qty >= 100 ? 10 : qty >= 50 ? 5 : 0);

            const recalc = () => {
                let total = 0;
                container.querySelectorAll('.item-row').forEach((row) => {
                    const qty = parseInt(row.querySelector('.quantity').value || '0', 10) || 0;
                    const price = parseFloat(prices[row.querySelector('.product-select').value] || '0');
                    const line = price * (1 - discountFor(qty) / 100) * qty;
                    row.querySelector('.line-price').textContent = '$' + line.toFixed(2);
                    total += line;
                });
                document.getElementById('grand-total').textContent = '$' + total.toFixed(2);
            };

            const addRow = (productId = '', quantity = 1) => {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replace(/__INDEX__/g, index++).trim();
                const row = wrapper.firstElementChild;
                if (productId) row.querySelector('.product-select').value = productId;
                row.querySelector('.quantity').value = quantity;
                container.appendChild(row);
                recalc();
            };

            document.getElementById('add-row').addEventListener('click', () => addRow());
            container.addEventListener('input', recalc);
            container.addEventListener('change', recalc);
            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-row')) {
                    event.target.closest('.item-row').remove();
                    recalc();
                }
            });

            (oldItems.length ? oldItems : [{}]).forEach((item) => addRow(item.product_id || '', item.quantity || 1));
        });
    </script>
@endpush
