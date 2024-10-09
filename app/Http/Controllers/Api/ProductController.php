<?php

namespace App\Http\Controllers\Api;

use App\Enums\Category;
use App\Models\product;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use HttpResponses;
    private $minutes = 60;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query();

        $page = $query['page'] ?? 1;
        $perPage = $query['perPage'] ?? 15;
        $search = $query['search'] ?? '';
        $all = $query['all'] ?? false;

        $products = Product::orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'products', $page);

        if ($search)
            $products = Product::where('name', 'like', "%$search%")
                ->orWhere('unit_price', 'like', "%$search%")
                ->orWhere('quantity', 'like', "%$search%")
                ->orderBy('created_at', 'asc')->paginate();

        if ($all)
            $products = Product::orderByDesc('created_at')->get();

        return $this->success(
            ['products' => $products],
            'Products retrieved successfully.',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit_price' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
            'category' => [
                'required',
                Rule::in(
                    array_column(Category::cases(), 'value')
                )
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Invalid input data',
                400,
            );
        }

        try {
            $product = Product::create([
                'name' => $request->name,
                'unit_price' => $request->unit_price,
                'quantity' => $request->quantity,
                'category' => $request->category,
            ]);

            return $this->success(
                ['product' => $product],
                'Product created successfully.',
                201,
            );

        } catch (\Exception $e) {
            return $this->error(
                $e,
                'Duplicate product name.',
                400,
            );
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $product): JsonResponse
    {
        $product = Cache::remember(
            'product', now()->addMinutes($this->minutes),
            function () use ($product) {
                return Product::findOrFail($product)->first();
            }
    );
        $product->orderDetails;

        return $this->success(
            ['product' => $product],
            'Product retrieved successfully.',
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'unit_price' => 'integer|min:1',
            'quantity' => 'integer|min:1',
            'category' => 'string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Invalid input data',
                400,
            );
        }

        $_product = Product::findOrFail($product);

        $updatedProduct = Product::where(['id' => $product])->update([
            'name' => isset($request->name) ? $request->name : $_product->name,
            'unit_price' => isset($request->unit_price) ? $request->unit_price : $_product->unit_price,
            'quantity' => isset($request->quantity) ? $request->quantity : $_product->quantity,
            'category' => isset($request->category) ? $request->category : $_product->category,
        ]);

        if ($updatedProduct != 1)
            return $this->error(
                null,
                'Product not found.',
                404,
            );

        return $this->success(
            ['isUpdated' => true],
            'Product updated successfully.',
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $product): JsonResponse
    {
        Product::where('id', $product)->delete();

        return $this->success(
            ['isDeleted' => true],
            'Product deleted successfully.',
            200,
        );
    }

    public function countProducts(): JsonResponse
    {
        $count = Product::count();

        return $this->success(
            ['totalProducts' => $count],
            'Products counted successfully.',
            200,
        );
    }
}
