<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\AddOrderItemAction;
use App\Actions\Orders\RemoveOrderItemAction;
use App\Actions\Orders\UpdateOrderItemAction;
use App\Http\Requests\StoreOrderItemRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

final class OrderItemController extends Controller
{
    public function store(
        StoreOrderItemRequest $request,
        Order $order,
        AddOrderItemAction $action,
    ): RedirectResponse {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        $action->execute($order, $product, $request->integer('quantity'));

        return redirect()->route('orders.show', $order)->with('status', 'Line item added.');
    }

    public function update(
        StoreOrderItemRequest $request,
        Order $order,
        Product $product,
        UpdateOrderItemAction $action,
    ): RedirectResponse {
        $action->execute($order, $product, $request->integer('quantity'));

        return redirect()->route('orders.show', $order)->with('status', 'Line item updated.');
    }

    public function destroy(
        Order $order,
        Product $product,
        RemoveOrderItemAction $action,
    ): RedirectResponse {
        $this->authorize('update', $order);

        $action->execute($order, $product);

        return redirect()->route('orders.show', $order)->with('status', 'Line item removed.');
    }
}
