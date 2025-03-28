<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;

class NewsController extends Controller
{


    // public function index() {
    //     $news = News::all()->map(function ($item) {
    //         if ($item->image) {
    //             // ✅ Ensure correct path format
    //             if (!str_starts_with($item->image, 'http')) {
    //                 $item->image = url($item->image); // Convert to full URL
    //             }
    //         }
    //         return $item;
    //     });
    
    //     return response()->json($news);
    // }



    public function index()
    {
        
        
        $news = News::with('images')->get(); // 👈 this loads related images
        return $news->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'content' => $item->content,
                'image' => $item->image, // main image (if you have this column)
                'images' => $item->images, // array of images from relation
                'archived' => $item->archived,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });
    }


    // Store a news article (for Admins)
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string',
    //         'content' => 'required|string',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);
    
    //     $news = new News();
    //     $news->title = $request->title;
    //     $news->content = $request->content;
    
    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('uploads/news', 'public');
    //         $news->image = '/storage/' . $imagePath; // Save full path
    //     }
    
    //     $news->save();
    
    //     return response()->json($news);
    // }

    // public function update(Request $request, $id)
    // {
    //     $news = News::findOrFail($id);
    
    //     $news->title = $request->title;
    //     $news->content = $request->content;
    
    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('uploads/news', 'public');
    //         $news->image = '/storage/' . $imagePath;
    //     }
    
    //     $news->archived = $request->archived ?? $news->archived;
    //     $news->save();
    
    //     return response()->json(['news' => $news]);
    // }




    public function show($id)
    {
        // Fetch the specific campaign by ID along with its images
        $news = News::with('images')->find($id);
    
        // Check if the campaign exists
        if (!$news) {
            return response()->json(['message' => 'Campaign not found'], 404); // Return error if not found
        }
    
        // Transform the campaign data (images URLs)
        if ($news->image && !str_starts_with($news->image, 'http')) {
            $news->image = url($news->image); // Convert to full URL
        }
    
        $news->images->transform(function ($img) {
            if (!str_starts_with($img->image, 'http')) {
                $img->image = asset($img->image); // Convert to full URL
            }
            return $img;
        });
    
        // Return the campaign with images as JSON
        return response()->json($news);
    }


public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'content' => 'required|string',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    ]);

    $news = new News();
    $news->title = $request->title;
    $news->content = $request->content;
    $news->save();

    // ✅ Save multiple uploaded images to news_images table
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads/news', 'public');

            \DB::table('news_images')->insert([
                'news_id' => $news->id,
                'image' => '/storage/' . $path, // ✅ correct column
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    return response()->json([
        'news' => $news->load('images'), // ✅ Load images with the response
        'message' => 'Campaign saved with images.',
    ]);
}



public function update(Request $request, News $news)
{
    try {
        // Validate only provided fields
        $rules = [
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];

        if ($request->has('title')) {
            $rules['title'] = 'required|string';
        }

        if ($request->has('content')) {
            $rules['content'] = 'required|string';
        }

        $request->validate($rules);

        \Log::info('🛠 Editing News', [
            'title' => $request->title ?? '[No Title]',
            'has_files' => $request->hasFile('images'),
            'file_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'archived' => $request->input('archived', 'not sent'),
        ]);

        // Update only provided fields
        $updateData = [];

        if ($request->has('title')) {
            $updateData['title'] = $request->title;
        }
        if ($request->has('content')) {
            $updateData['content'] = $request->content;
        }
        if ($request->has('archived')) {
            $updateData['archived'] = (int) $request->archived; // Force to integer
        }

        $news->update($updateData);

        // Handle removed images
        if ($request->has('removed_image_ids')) {
            $ids = json_decode($request->removed_image_ids, true);
            \Log::info('🧹 Removing images with IDs', ['ids' => $ids]);

            NewsImage::whereIn('id', $ids)->get()->each(function ($image) {
                try {
                    $relativePath = str_replace('/storage/', '', $image->image);
                    Storage::delete('public/' . $relativePath);
                    $image->delete();
                } catch (\Exception $e) {
                    \Log::error('❌ Error deleting image', [
                        'id' => $image->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('uploads/news', 'public');  // Store the file in the public directory

                NewsImage::create([
                    'news_id' => $news->id, // Link the image to the news item
                    'image' => '/storage/' . $path, // Store the relative path to the image
                ]);
            }
        }

        return response()->json([
            'news' => $news->load('images'),  // Return the updated news with its associated images
            'message' => 'Update successful',
        ]);
    } catch (\Exception $e) {
        \Log::error('❌ Update Error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json(['error' => 'Update failed.'], 500);
    }
}







public function archive($id)
{
    $news = News::find($id);
    if (!$news) {
        return response()->json(['error' => 'News not found'], 404);
    }
    $news->archived = 1;
    $news->save();
    return response()->json(['news' => $news, 'message' => 'Campaign archived']);
}

public function unarchive($id)
{
    $news = News::find($id);
    if (!$news) {
        return response()->json(['error' => 'News not found'], 404);
    }
    $news->archived = 0;
    $news->save();
    return response()->json(['news' => $news, 'message' => 'Campaign unarchived']);
}







public function activeCampaigns()
{
    $news = News::with('images')
        ->where('archived', 0) // Only active campaigns
        ->get();

    return $news->map(function ($item) {
        $itemArray = $item->toArray();

        // Handle main image (optional field)
        $itemArray['image'] = $item->image 
            ? url('storage/' . $item->image)
            : null;

        // Handle related images
        $itemArray['images'] = $item->images->map(function ($image) {
            return [
                'id' => $image->id,
                'image' => url('storage/' . $image->image),
            ];
        });

        return $itemArray;
    });
}


}
