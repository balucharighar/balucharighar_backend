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
            'category_id' => 'nullable|integer',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:flat,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'demo_link' => 'nullable|url',
            // image optional
            // 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
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
            'image' => $imagePath,
            'price' => $request->price,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'final_price' => $finalPrice,
            'stock' => $request->stock ?? 0,
            'sku' => $request->sku,
            'demo_link' => $request->demo_link,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
}
