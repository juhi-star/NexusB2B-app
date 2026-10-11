<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isCreditManager()) {
            $pendingOrders = Order::query()
                ->with(['customer', 'salesRepresentative'])
                ->where('status', OrderStatus::Submitted)
                ->latest()
                ->get();

            $recentOrders = Order::query()
                ->with(['customer', 'salesRepresentative'])
                ->latest()
                ->limit(10)
                ->get();

            return view('dashboard', compact('pendingOrders', 'recentOrders'));
        }

        $orders = Order::query()
            ->with('customer')
            ->where('created_by', $user->getKey())
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact('orders'));
    }
}
