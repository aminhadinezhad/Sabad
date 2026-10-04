<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::singles()->orderBy('name')->get();

        // «سبد اختصاصی»: the bundles, cheapest first, with their products for the line under the
        // name, the VAT and whether any of them is out of stock
        $bundles = Product::bundles()->with('bundleItems.product')->orderBy('price')->orderBy('name')->get();

        return view('products.index', ['products' => $products, 'bundles' => $bundles]);
    }
}
