<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // ✅ Get User Reviews
    public function index()
    {
        $user = Auth::user();

        $reviews = Review::where('user_id', $user->id)->with('order')->get();

        return response()->json($reviews);
    }

    // ✅ Create a Review (Only 1 per Order)
    public function store(Request $request)
    {
        $user = Auth::user();

        // ✅ Check if the order exists and belongs to the user
        $order = Order::where('id', $request->order_id)->where('user_id', $user->id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found or does not belong to you.'], 403);
        }

        // ✅ Check if the user already left a review for this order
        $existingReview = Review::where('order_id', $request->order_id)->where('user_id', $user->id)->first();
        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this order.'], 400);
        }

        // ✅ Store the review
        $review = new Review();
        $review->user_id = $user->id;
        $review->order_id = $request->order_id;
        $review->comment = $request->comment;

        // ✅ Handle Image Upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('reviews', 'public');
            $review->image = $path;
        }

        $review->save();

        return response()->json(['message' => 'Review submitted successfully!', 'review' => $review], 201);
    }





    //for landing page

    public function getUserReviews()
    {
        try {
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $reviews = Review::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

            return response()->json($reviews, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllReviews()
    {
        try {
            $reviews = Review::with('user:id,name') // ✅ Include user's name
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($reviews, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }






    public function publish($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'Published']);
    
        return response()->json([
            'message' => 'Review published successfully!',
            'status' => 'Published'
        ]);
    }
    

    
    public function unpublish($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'Unpublished']);
    
        return response()->json([
            'message' => 'Review unpublished successfully!',
            'status' => 'Unpublished'
        ]);
    }







    public function togglePublish($id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->published = !$review->published; // Toggle published status
            $review->save();
    
            return response()->json(['message' => 'Review status updated successfully', 'published' => $review->published], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update review status'], 500);
        }
    }
    
}
