<?php

namespace App\Http\Controllers;

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
            'category_id' => 'required|integer',
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

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = url('storage/' . $path);
        }

        $finalPrice = $request->price;
        $isFakeDiscount = filter_var($request->is_fake_discount, FILTER_VALIDATE_BOOLEAN);

        if ($request->discount_type && $request->discount_value && !$isFakeDiscount) {
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
            'image' => $imageUrl,
            'price' => $request->price,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'is_fake_discount' => $isFakeDiscount,
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

    public function getProduct(Request $request)
    {
        $products = Product::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->when(isset($request->min_price) && isset($request->max_price), function ($q) use ($request) {
                $q->whereBetween('price', [$request->min_price, $request->max_price]);
            })
            ->when(isset($request->min_price) && !isset($request->max_price), function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            })
            ->when(!isset($request->min_price) && isset($request->max_price), function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'product' => $products
        ]);
    }

    public function getProductById($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'product' => $product
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'category_id' => 'sometimes|integer',
            'discount_type' => 'nullable|in:flat,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $id,
            'demo_link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = url('storage/' . $path);
        }

        if ($request->has('name')) {
            $product->name = $request->name;
            $slug = Str::slug($request->name);
            $count = Product::where('slug', 'like', $slug . '%')->where('id', '!=', $id)->count();
            $product->slug = $count > 0 ? $slug . '-' . ($count + 1) : $slug;
        }

        $price = $request->price ?? $product->price;
        $discountType = $request->discount_type ?? $product->discount_type;
        $discountValue = $request->discount_value ?? $product->discount_value;
        $isFakeDiscount = $request->has('is_fake_discount') 
            ? filter_var($request->is_fake_discount, FILTER_VALIDATE_BOOLEAN) 
            : $product->is_fake_discount;

        $finalPrice = $price;
        if ($discountType && $discountValue && !$isFakeDiscount) {
            if ($discountType === 'flat') {
                $finalPrice = max(0, $price - $discountValue);
            }
            if ($discountType === 'percent') {
                $finalPrice = max(0, $price - ($price * $discountValue / 100));
            }
        }

        $product->fill($request->only([
            'category_id', 'short_description', 'description',
            'price', 'discount_type', 'discount_value', 'stock', 'sku', 'demo_link'
        ]));
        $product->is_fake_discount = $isFakeDiscount;
        $product->final_price = $finalPrice;
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
