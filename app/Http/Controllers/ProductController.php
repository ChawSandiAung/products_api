<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // GET /api/products
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->withCount('variants')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    // GET /api/products/{id}
    public function show(int $id): JsonResponse
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json(new ProductResource($product));
    }

    // POST /api/products
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'base_price' => $validated['base_price'],
                'slug' => $validated['slug'] ?? null,
            ]);

            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $v) {
                    $product->variants()->create([
                        'carat' => $v['carat'] ?? null,
                        'metal_type' => $v['metal_type'],
                        'price' => $v['price'],
                        'stock' => $v['stock'],
                        'sku' => $v['sku'],
                    ]);
                }
            }

            $product->load('variants');

            return response()->json(new ProductResource($product), 201);
        });
    }

    // PUT /api/products/{id}
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($product, $validated) {
            // Update product fields
            $product->fill(array_intersect_key($validated, array_flip(['name','description','base_price','slug'])));
            $product->save();

            // Handle variants update (upsert-like behavior)
            if (array_key_exists('variants', $validated)) {
                foreach ($validated['variants'] as $v) {
                    if (!empty($v['id'])) {
                        // Update existing variant
                        $variant = $product->variants()->where('id', $v['id'])->first();
                        if ($variant) {
                            $variant->fill(array_intersect_key($v, array_flip(['carat','metal_type','price','stock','sku'])));
                            $variant->save();
                        }
                    } else {
                        // Create new variant
                        $product->variants()->create([
                            'carat' => $v['carat'] ?? null,
                            'metal_type' => $v['metal_type'] ?? 'gold',
                            'price' => $v['price'] ?? 0,
                            'stock' => $v['stock'] ?? 0,
                            'sku' => $v['sku'],
                        ]);
                    }
                }
            }

            // Handle variants deletion (soft delete)
            if (!empty($validated['variants_to_delete'])) {
                $product->variants()
                    ->whereIn('id', $validated['variants_to_delete'])
                    ->get()
                    ->each->delete();
            }

            $product->load('variants');

            return response()->json(new ProductResource($product));
        });
    }

    // DELETE /api/products/{id}
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return DB::transaction(function () use ($product) {
            // Soft delete variants first for clarity (cascadeOnDelete would hard delete; we want soft)
            $product->variants()->each(function (Variant $v) {
                $v->delete();
            });

            $product->delete();

            return response()->json(null, 204);
        });
    }
}