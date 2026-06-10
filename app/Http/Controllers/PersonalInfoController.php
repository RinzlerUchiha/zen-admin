<?php

namespace App\Http\Controllers;

use App\Models\PersonalInfo;
use Illuminate\Http\Request;

class PersonalInfoController extends Controller
{
    public function index()
    {
        $customers = PersonalInfo::with('addresses', 'contacts')->get(); // Eager load addresses and contacts
        return view('customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = PersonalInfo::with('addresses', 'contacts')->findOrFail($id);
        return view('customers.show', compact('customer'));
    }
}
