<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        $donations = $this->donation->orderBy('id', 'desc')->paginate(8);
        return view('donation', compact('donations'));
    }

    public function create()
    {
        return view('donation');
    }

    public function store(Request $request)
    {
        $this->db::transaction(function () use ($request) {
            $donation = $this->donation->create([
                'donor_name' => $request->donor_name,
                'donor_email' => $request->donor_email,
                'donation_type' => $request->donation_type,
                'amount' => floatval($request->amount),
                'note' => $request->note,
            ]);

            $payload = [
                'transaction_details' => [
                    'order_id'      => $donation->id,
                    'gross_amount'  => $donation->amount,
                ],
                'customer_details' => [
                    'first_name'    => $donation->donor_name,
                    'email'         => $donation->donor_email,
                    // 'phone'         => '08888888888',
                    // 'address'       => '',
                ],
                'item_details' => [
                    [
                        'id'       => $donation->donation_type,
                        'price'    => $donation->amount,
                        'quantity' => 1,
                        'name'     => ucwords(str_replace('_', ' ', $donation->donation_type))
                    ]
                ]
            ];
            $snapToken = \Midtrans\Snap::getSnapToken($payload);
            $donation->snap_token = $snapToken;
            $donation->save();

            $this->response['snap_token'] = $snapToken;
        });

        return response()->json($this->response);
    }

    public function notification(Request $request)
    {
        $notif = new \Midtrans\Notification();
        $this->db::transaction(function () use ($notif) {

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;
            $donation = $this->donation->findOrFail($orderId);

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {

                    if ($fraud == 'challenge') {
                        $donation->setStatusPending();
                    } else {
                        $donation->setStatusSuccess();
                    }
                }
            } elseif ($transaction == 'settlement') {

                $donation->setStatusSuccess();
            } elseif ($transaction == 'pending') {

                $donation->setStatusPending();
            } elseif ($transaction == 'deny') {

                $donation->setStatusFailed();
            } elseif ($transaction == 'expire') {

                $donation->setStatusExpired();
            } elseif ($transaction == 'cancel') {

                $donation->setStatusFailed();
            }
        });

        return;
    }
}