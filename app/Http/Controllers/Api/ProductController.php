<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);

        $cacheKey = 'products:list:' . md5(serialize(compact('search', 'category', 'limit', 'page')));

        $products = Cache::store('redis')->remember($cacheKey, 60, function () use ($search, $category, $limit) {
            $query = Product::query();

            if ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
            }

            if ($category) {
                $query->where('category', $category);
            }

            return $query->paginate($limit)->toArray();
        });

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $cacheKey = 'products:show:' . $id;

        $productData = Cache::store('redis')->remember($cacheKey, 60, function () use ($id) {
            $product = Product::find($id);

            return $product ? (new ProductResource($product))->resolve() : null;
        });

        if (!$productData) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(new ProductResource((object) $productData));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $product = Product::create([
            ...$request->validated(),
            'created_by' => $user->name ?: $user->username,
            'created_by_id' => $user->id,
            'updated_by' => $user->name ?: $user->username,
            'updated_by_id' => $user->id,
        ]);

        $this->bustListCache();

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $user = auth('api')->user();

        $product->update([
            ...$request->validated(),
            'updated_by' => $user->name ?: $user->username,
            'updated_by_id' => $user->id,
        ]);

        $this->bustProductCache($id);
        $this->bustListCache();

        return (new ProductResource($product->fresh()))->response()->setStatusCode(200);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        $this->bustProductCache($id);
        $this->bustListCache();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function bustProductCache(int $id): void
    {
        try {
            Cache::store('redis')->forget('products:show:' . $id);
        } catch (\Throwable $e) {

        }
    }

    private function bustListCache(): void
    {
        try {
            $redis = Cache::store('redis')->getStore()->connection();
            $keys = $redis->keys('*products:list:*');

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $redis->del($key);
                }
            }
        } catch (\Throwable $e) {

        }
    }
}
