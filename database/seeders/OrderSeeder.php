<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Orders\ApproveOrderAction;
use App\Actions\Orders\CreateDraftOrderAction;
use App\Actions\Orders\DispatchOrderAction;
use App\Actions\Orders\RejectOrderAction;
use App\Actions\Orders\SubmitOrderAction;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    /**
     * Build a representative cross-section of orders by driving the real
     * domain actions, so seeded prices, totals, stock and balances stay
     * consistent with runtime behaviour.
     */
    public function run(
        CreateDraftOrderAction $create,
        SubmitOrderAction $submit,
        ApproveOrderAction $approve,
        RejectOrderAction $reject,
        DispatchOrderAction $dispatch,
    ): void {
        if (Order::query()->exists()) {
            return;
        }

        $manager = User::query()->where('email', 'manager@nexusb2b.test')->firstOrFail();
        $rep = User::query()->where('email', 'rep@nexusb2b.test')->firstOrFail();
        $rep2 = User::query()->where('email', 'rep2@nexusb2b.test')->firstOrFail();

        $customerId = Customer::query()->pluck('id', 'company_name');
        $productId = Product::query()->pluck('id', 'sku');

        $draft = fn (User $owner, string $company, array $lines): Order => $create->execute(
            $owner,
            Customer::query()->findOrFail($customerId[$company]),
            $lines,
        );

        // 1. Draft — left editable for the rep demo.
        $draft($rep, 'Acme Distribution', [
            ['product_id' => $productId['SKU-STL-001'], 'quantity' => 10],
            ['product_id' => $productId['SKU-RUB-004'], 'quantity' => 60],
        ]);

        // 2. Draft.
        $draft($rep2, 'Brightline Supplies', [
            ['product_id' => $productId['SKU-ALU-003'], 'quantity' => 100],
        ]);

        // 3. Submitted — waiting in the manager review queue.
        $submit->execute($draft($rep, 'Northwind Grocers', [
            ['product_id' => $productId['SKU-SEN-007'], 'quantity' => 5],
            ['product_id' => $productId['SKU-FAS-006'], 'quantity' => 25],
        ]));

        // 4. Approved.
        $approve->execute($submit->execute($draft($rep2, 'Brightline Supplies', [
            ['product_id' => $productId['SKU-COP-002'], 'quantity' => 20],
        ])), $manager);

        // 5. Approved and dispatched.
        $dispatch->execute($approve->execute($submit->execute($draft($rep, 'Acme Distribution', [
            ['product_id' => $productId['SKU-RUB-004'], 'quantity' => 100],
        ])), $manager));

        // 6. Rejected with a reason.
        $reject->execute(
            $submit->execute($draft($rep2, 'Cobalt Traders', [
                ['product_id' => $productId['SKU-HYD-005'], 'quantity' => 1],
            ])),
            $manager,
            'Order exceeds the approved credit limit for this billing cycle.',
        );
    }
}
