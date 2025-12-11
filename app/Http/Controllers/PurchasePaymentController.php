<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\Auth;

class PurchasePaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'    => 'required|exists:purchases,id',
            'payment_method' => 'required|string',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'nullable|date',
        ]);

        $payment = PurchasePayment::create([
            'purchase_id'   => $request->purchase_id,
            'payment_method'=> $request->payment_method,
            'amount'        => $request->amount,
            'payment_date'  => $request->payment_date ?? now(),
            'created_by'    => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Payment added successfully.');
    }
}
