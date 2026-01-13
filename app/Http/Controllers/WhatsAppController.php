<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required',
            'message' => 'required',
        ]);

        $twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $twilio->messages->create(
            'whatsapp:' . $request->to,
            [
                'from' => config('services.twilio.whatsapp_from'),
                'body' => $request->message,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp message sent'
        ]);
    }
}
