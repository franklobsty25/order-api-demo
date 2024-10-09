<?php

namespace App\Http\Controllers\Api;

use App\Models\orderDetail;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class OrderDetailController extends Controller
{
    use HttpResponses;

    private $limit = 10;
    private $minutes = 60;
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {

        $order_details = OrderDetail::orderByDesc('created_at')
            ->paginate($this->limit);

        return $this->success(
            ['order_details' => $order_details],
            'Order details retrieved successfully.',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $orderDetail): JsonResponse
    {
        $orderDetail = Cache::remember(
            'order_detail', now()->addMinutes($this->minutes),
            function () use ($orderDetail) {
                return orderDetail::findOrFail($orderDetail);
            }
    );
        $orderDetail->order;
        $orderDetail->product;

        return $this->success(
            ['orderDetail' => $orderDetail],
            'Order details retrieved successfully.',
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $orderDetail): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Invalid input data',
                400,
            );
        }

        $updatedDetail = OrderDetail::where('id', $orderDetail)
            ->update(['quantity' => $request->quantity]);

        if ($updatedDetail != 1)
            return $this->error(
                null,
                'Order detail not found, update failed!',
                404,
            );

        return $this->success(
            ['isUpdated' => true],
            'Order detail updated successfully.',
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $orderDetail): JsonResponse
    {
        orderDetail::findOrFail($orderDetail)->delete();

        return $this->success(
            ['isDeleted' => true],
            'Order detail deleted successfully.',
            200,
        );
    }
}
