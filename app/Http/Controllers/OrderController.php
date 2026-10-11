<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\ApproveOrderAction;
use App\Actions\Orders\CreateDraftOrderAction;
use App\Actions\Orders\DispatchOrderAction;
use App\Actions\Orders\RejectOrderAction;
use App\Actions\Orders\SubmitOrderAction;
use App\Enums\CustomerStatus;
use App\Enums\OrderStatus;
use App\Http\Requests\ReviewOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\SubmitOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderCreditEvaluator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with(['customer', 'salesRepresentative'])
            ->when(
                $request->user()->isSalesRepresentative(),
                fn ($query) => $query->where('created_by', $request->user()->getKey()),
            )
            ->when(
                $request->string('status')->toString(),
                fn ($query, $status) => $query->where('status', $status),
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Order::class);

        return view('orders.create', [
            'customers' => Customer::query()
                ->where('status', CustomerStatus::Active->value)
                ->orderBy('company_name')
                ->get(),
            'products' => Product::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreOrderRequest $request, CreateDraftOrderAction $action): RedirectResponse
    {
        $order = $action->execute(
            $request->user(),
            Customer::query()->findOrFail($request->integer('customer_id')),
            $request->validated('items'),
        );

        return redirect()->route('orders.show', $order)->with('status', 'Draft order created.');
    }

    public function show(Order $order, OrderCreditEvaluator $credit): View
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'salesRepresentative', 'approvedBy', 'rejectedBy', 'items.product']);

        $products = $order->status === OrderStatus::Draft
            ? Product::query()
                ->where('is_active', true)
                ->whereNotIn('id', $order->items->pluck('product_id'))
                ->orderBy('name')
                ->get()
            : collect();

        return view('orders.show', [
            'order' => $order,
            'availableCredit' => $credit->availableCredit($order->customer),
            'products' => $products,
        ]);
    }

    public function submit(Order $order, SubmitOrderRequest $request, SubmitOrderAction $action): RedirectResponse
    {
        $action->execute($order);

        return redirect()->route('orders.show', $order)->with('status', 'Order submitted for credit review.');
    }

    public function review(
        Order $order,
        ReviewOrderRequest $request,
        ApproveOrderAction $approve,
        RejectOrderAction $reject,
    ): RedirectResponse {
        if ($request->validated('decision') === 'reject') {
            $reject->execute($order, $request->user(), (string) $request->validated('remarks'));

            return redirect()->route('orders.show', $order)->with('status', 'Order rejected.');
        }

        $approve->execute($order, $request->user());

        return redirect()->route('orders.show', $order)->with('status', 'Order approved and stock released.');
    }

    public function dispatch(Order $order, DispatchOrderAction $action): RedirectResponse
    {
        $this->authorize('dispatch', $order);

        $action->execute($order);

        return redirect()->route('orders.show', $order)->with('status', 'Order marked as dispatched.');
    }
}
