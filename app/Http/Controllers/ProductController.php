<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',

            'short_description' => 'nullable|string|max:255|required_without:description',
            'description' => 'nullable|string|required_without:short_description',

            'price' => 'required|numeric|min:0',

            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',

            'category_id' => 'nullable|integer',
            'discount_type' => 'nullable|in:flat,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'demo_link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = Str::slug($request->name);
        $count = Product::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $finalPrice = $request->price;

        if ($request->discount_type && $request->discount_value) {
            if ($request->discount_type === 'flat') {
                $finalPrice = max(0, $request->price - $request->discount_value);
            }

            if ($request->discount_type === 'percent') {
                $finalPrice = max(
                    0,
                    $request->price - ($request->price * $request->discount_value / 100)
                );
            }
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'image' => $imagePath, // DB me sirf path
            'price' => $request->price,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'final_price' => $finalPrice,
            'stock' => $request->stock ?? 0,
            'sku' => $request->sku,
            'demo_link' => $request->demo_link,
        ]);

        $product->image_url = url('storage/' . $product->image);

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }


    public function getProduct(Request $request)
    {

        $products = Product::query()

            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })

            ->when($request->category, function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })

            ->when($request->min_price, function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            })

            ->when($request->max_price, function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            })

            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'product' => $products
        ]);
    }
}
