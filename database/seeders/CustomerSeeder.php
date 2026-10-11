<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Seeder;

final class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['company_name' => 'Acme Distribution', 'email' => 'ops@acme.test', 'phone' => '555-0100', 'credit_limit' => 50000, 'outstanding_balance' => 5000, 'status' => CustomerStatus::Active],
            ['company_name' => 'Brightline Supplies', 'email' => 'purchasing@brightline.test', 'phone' => '555-0142', 'credit_limit' => 20000, 'outstanding_balance' => 0, 'status' => CustomerStatus::Active],
            ['company_name' => 'Cobalt Traders', 'email' => 'accounts@cobalttraders.test', 'phone' => '555-0177', 'credit_limit' => 10000, 'outstanding_balance' => 9500, 'status' => CustomerStatus::Active],
            ['company_name' => 'Northwind Grocers', 'email' => 'supply@northwind.test', 'phone' => '555-0193', 'credit_limit' => 35000, 'outstanding_balance' => 12000, 'status' => CustomerStatus::Active],
            ['company_name' => 'Dormant Retail Co', 'email' => 'info@dormantretail.test', 'phone' => null, 'credit_limit' => 15000, 'outstanding_balance' => 0, 'status' => CustomerStatus::Inactive],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['company_name' => $customer['company_name']],
                [
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'credit_limit' => $customer['credit_limit'],
                    'outstanding_balance' => $customer['outstanding_balance'],
                    'status' => $customer['status']->value,
                ],
            );
        }
    }
}
