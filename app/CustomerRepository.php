<?php

namespace App;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;

class CustomerRepository
{
    public function createCustomer(CustomerRequest $request): Customer
    {
        return Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phonenumber' => $request->phonenumber,
            'email' => $request->email,
            'address' => $request->address,
        ]);
    }
}
