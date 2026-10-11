@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Products</h1>
        <p class="mt-1 text-sm text-slate-500">Active catalogue available for ordering.</p>
    </div>

    <div class="overflow-hidden overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">SKU</th>
                    <th class="px-6 py-3 text-right">Base price</th>
                    <th class="px-6 py-3 text-right">In stock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3">
                            <div class="font-medium text-slate-900">{{ $product->name }}</div>
                            @if ($product->description)
                                <div class="text-xs text-slate-400">{{ $product->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $product->sku }}</td>
                        <td class="px-6 py-3 text-right text-slate-700">${{ number_format((float) $product->base_price, 2) }}</td>
                        <td class="px-6 py-3 text-right">
                            <span class="font-medium {{ $product->stock_quantity > 0 ? 'text-slate-900' : 'text-red-600' }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
