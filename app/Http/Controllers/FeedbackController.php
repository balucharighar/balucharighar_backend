<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'useful' => 'required|string',
            'pay' => 'required|string',
            'price' => 'required|string',
            'rating' => 'required|integer|min:1|max:10',

            'name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'liked' => 'nullable|string',
            'confused' => 'nullable|string',
            'improvement' => 'nullable|string',
            'feature' => 'nullable|string',
        ]);

        $feedback = Feedback::create($validated);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);
    }

    public function index()
    {
        $feedbacks = Feedback::latest()->get();

        return response()->json([
            'data' => $feedbacks
        ]);
    }

    public function show($id)
    {
        $feedback = Feedback::findOrFail($id);

        return response()->json([
            'data' => $feedback
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total_feedbacks' => Feedback::count(),
            'average_rating' => round(Feedback::avg('rating'), 1),
            'very_useful_count' => Feedback::where('useful', 'Very useful')->count(),
            'will_pay_yes' => Feedback::where('pay', 'Yes')->count(),
        ]);
    }
}
