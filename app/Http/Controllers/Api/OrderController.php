<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\order;
use App\Jobs\OrderJob;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Mail\OrderPurchased;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use HttpResponses;
    private $minutes = 60;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->query();

        $page = $query['page'] ?? 1;
        $perPage = $query['perPage'] ?? 15;
        $search = $query['search'] ?? '';
        $all = $query['all'] ?? false;

        $orders = Order::orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'orders', $page);

        if ($search)
            $orders = Order::where('amount', 'like', "%$search%")
                ->orderBy('created_at', 'asc')->paginate();

        if ($all == 'true')
            $orders = Order::orderByDesc('created_at')->get();

        foreach ($orders as $order) {
            $order->customer;
        }

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'totalAmount' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Invalid input data',
                400,
            );
        }

        $products = $request->products;
        $_customer = Customer::findOrFail($customer);

        $order = $_customer->orders()->save(new Order([
            'amount' => $request->totalAmount,
            'status' => 'completed',
            'customer_id' => $customer,
        ]));

        $order->orderDetails()->saveMany($this->mapOrderDetails($products));

        OrderJob::dispatch($order);

        // Mail::to($request->user())
        //     ->send(new OrderPurchased($_customer, $order));

        return $this->success(
            ['order' => $order],
            'Order created successfully.',
            201,
        );
    }

    private function mapOrderDetails(array $products): array
    {
        return array_map(function ($product) {
            return new OrderDetail([
                'quantity' => $product['quantity'],
                'product_id' => $product['productId'],
            ]);
        }, $products);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $order): JsonResponse
    {
        $order = Order::findOrFail($order);
        $orderDetails = $order->orderDetails;
        $order->customer;

        foreach($orderDetails as $orderDetail) {
            $orderDetail->product;
        }

        return $this->success(
            ['order' => $order],
            'Order retrieved successfully.',
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Order status updated successfully.',
                200,
            );
        }

        $updated = Order::where('id', $order)
            ->update(['status' => $request->status]);

        if ($updated != 1)
            return $this->error(
                null,
                'Order not found, update failed!',
                404,
            );

        return $this->success(
            ['isUpdated' => true],
            'Order updated successfully.',
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $order): JsonResponse
    {
        Order::where('id', $order)->delete();

        return $this->success(
            ['isDeleted' => true],
            'Order deleted successfully.',
            200,
        );
    }

    public function orderStats(): JsonResponse
    {
        $totalCounts = Order::count();

        $completedOrders = Order::where('status', 'completed')->count();

        $pendingOrders = Order::where('status', 'pending')->count();

        $totalIncome = DB::table('orders')->sum('amount');

        return $this->success(
            [
                'totalOrders' => $totalCounts,
                'completedOrders' => $completedOrders,
                'pendingOrders' => $pendingOrders,
                'totalIncome' => (int) $totalIncome,
        ],
            'Orders stats retrieved successfully.',
            200,
        );
    }
}
