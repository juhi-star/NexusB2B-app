<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(15);

        return view('products.index', compact('products'));
    }
}
