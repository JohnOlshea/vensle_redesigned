<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeController extends Controller
{
	public function payment(Request $request)
	{
		$products = Product::whereIn('id', $request->product_ids)->get();
		$lineItems = [];

		foreach ($products as $product) {
		    $priceInCents = $product->price * 100; // Convert price to cents
		    $lineItems[] = [
			'price_data' => [
			    'currency' => 'usd',
			    'unit_amount' => $priceInCents,
			    'product_data' => [
				'name' => $product->name,
				'description' => $product->description,
			    ],
			],
			'quantity' => 1,
		    ];
		}

		Stripe::setApiKey(config('stripe.secret_key'));

		$session = Session::create([
		    'payment_method_types' => ['card', 'cashapp', 'us_bank_account'],
		    'line_items' => $lineItems,
		    'mode' => 'payment',
		    'success_url' => 'http://localhost:3000/payment/success',
		    'cancel_url' => 'http://localhost:3000/payment/cancel',
		]);

		return response()->json(['url' => $session->url]);
	}
}
