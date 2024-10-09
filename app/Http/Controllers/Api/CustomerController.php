<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
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

        $customers = Customer::orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'customers', $page);

        if ($search)
            $customers = Customer::where('firstname', 'like', "%$search%")
                ->orWhere('lastname', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orderBy('created_at', 'asc')->paginate();

        if ($all)
            $customers = Customer::orderByDesc('created_at')->get();

        return $this->success(
            ['customers' => $customers],
            'Customers retrieved successfully.',
            200,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phonenumber' => 'required|string|min:10|max:13',
            'email' => ['required', 'string', 'email', 'unique:customers'],
            'address' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error($errors, 'Invalid input data', 400);
        }

        $customer = Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phonenumber' => $request->phonenumber,
            'email' => $request->email,
            'address' => $request->address,
        ]);

        return $this->success(
            ['customer' => $customer],
            'Customer created successfully.',
            201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $customer): JsonResponse
    {
        $customer = Cache::remember(
            'customer',
            now()->addMinutes($this->minutes),
            function () use ($customer) {
                return Customer::findOrFail($customer)->first();
            }
        );
        $customer->orders;

        return $this->success(
            ['customer' => $customer],
            'Customer retrieved successfully.',
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $customer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'phonenumber' => 'string|min:10|max:13',
            'email' => 'string|email|unique.customers',
            'address' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors,
                'Invalid input data',
                400,
            );
        }

        $_customer = Customer::findOrFail($customer);

        $customerUpdated = Customer::where('id', $customer)->update([
            'firstname' => isset($request->firstname) ? $request->firstname : $_customer->firstname,
            'lastname' => isset($request->lastname) ? $request->lastname : $_customer->lastname,
            'phonenumber' => isset($request->phonenumber) ? $request->phonenumber : $_customer->phonenumber,
            'email' => isset($request->email) ? $request->email : $_customer->email,
            'address' => isset($request->address) ? $request->address : $_customer->address,
        ]);

        if ($customerUpdated !== 1)
            return $this->error(
                null,
                'Customer record updating failed!',
                422,
            );

        return $this->success(
            ['isUpdated' => true],
            'Customer record updated successfully.',
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $customer): JsonResponse
    {
        Customer::where('id', $customer)->delete();

        return $this->success(
            ['isDeleted' => true],
            'Customer record deleted successfully.',
            200,
        );
    }

    public function countCustomers(): JsonResponse
    {
        $count = Customer::count();

        return $this->success(
            ['totalCustomers' => $count],
            'Customers counted successfully.',
            200,
        );
    }
}
