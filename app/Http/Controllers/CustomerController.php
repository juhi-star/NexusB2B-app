<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\OrderCreditEvaluator;
use Illuminate\View\View;

final class CustomerController extends Controller
{
    public function __construct(
        private readonly OrderCreditEvaluator $credit,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()->orderBy('company_name')->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $orders = $customer->orders()
            ->with('salesRepresentative')
            ->latest()
            ->limit(10)
            ->get();

        return view('customers.show', [
            'customer' => $customer,
            'availableCredit' => $this->credit->availableCredit($customer),
            'orders' => $orders,
        ]);
    }
}
