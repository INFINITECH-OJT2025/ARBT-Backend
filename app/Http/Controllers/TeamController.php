<?php

// TeamController.php

// namespace App\Http\Controllers;

// use App\Models\Team;
// use Illuminate\Http\Request;

// class TeamController extends Controller
// {
//     // Show all teams
//     public function index()
//     {
//         $teams = Team::all(); // Fetch all team members
    
//         // Ensure the image URL is correctly formatted
//         foreach ($teams as $team) {
//             if ($team->image_url) {
//                 // Correct the image URL using the `asset()` helper
//                 $team->image_url = asset('storage/team_images/' . $team->image_url);
//             }
//         }
    
//         return response()->json($teams); // Return as JSON with correctly formatted URLs
//     }


//     // Store a new team
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'role' => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000', // Increase max to 5MB (5000KB)
//             'status' => 'required|in:Active,Archived',
//         ]);
    
//         // Handle image upload
//         if ($request->hasFile('image_url')) {
//             $image = $request->file('image_url');
//             $imageName = time() . '.' . $image->getClientOriginalExtension(); // Generate unique image name
//             $image->move(public_path('storage/team_images'), $imageName); // Move image to storage folder
//         } else {
//             $imageName = null;  // No image uploaded
//         }
    
//         // Create the new team
//         $team = Team::create([
//             'name' => $request->name,
//             'role' => $request->role,
//             'description' => $request->description,
//             'image_url' => $imageName,  // Store the image name or null if no image
//             'status' => $request->status,
//         ]);
    
//         return response()->json(['message' => 'Team member added successfully', 'team' => $team], 201);
//     }

//     // Update an existing team member
//     public function update(Request $request, Team $team)
//     {
//         // Validate the request
//         $validatedData = $request->validate([
//             'name' => 'nullable|string|max:255',
//             'role' => 'nullable|string|max:255',
//             'description' => 'nullable|string',
//             'status' => 'nullable|in:Active,Archived',
//             'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//         ]);
    
//         // ✅ Ensure existing values are kept if not updated
//         $team->name = $request->input('name', $team->name);
//         $team->role = $request->input('role', $team->role);
//         $team->description = $request->input('description', $team->description);
//         $team->status = $request->input('status', $team->status);
    
//         // ✅ Handle Image Upload
//         if ($request->hasFile('image_url')) {
//             if ($team->image_url && file_exists(public_path('storage/team_images/' . $team->image_url))) {
//                 unlink(public_path('storage/team_images/' . $team->image_url)); // Delete old image
//             }
    
//             $image = $request->file('image_url');
//             $imageName = time() . '.' . $image->getClientOriginalExtension();
//             $image->move(public_path('storage/team_images'), $imageName);
//             $team->image_url = $imageName;
//         }
    
//         // ✅ Save the updated team
//         $team->save();
    
//         return response()->json(['message' => 'Team member updated successfully', 'team' => $team], 200);
//     }
//     // Delete a team member
//     public function destroy(Team $team)
//     {
//         // Delete the team member image if exists
//         if ($team->image_url && file_exists(public_path('storage/team_images/' . $team->image_url))) {
//             unlink(public_path('storage/team_images/' . $team->image_url));
//         }

//         $team->delete(); // Delete team member
//         return response()->json(['message' => 'Team member deleted successfully'], 200);
//     }
// }





// namespace App\Http\Controllers;

// use App\Models\Team;
// use Illuminate\Http\Request;

// class TeamController extends Controller
// {
//     // Show all teams
//     public function index()
//     {
//         $teams = Team::all(); // Fetch all team members
    
//         // Ensure the image URL is correctly formatted
//         foreach ($teams as $team) {
//             if ($team->image_url) {
//                 // Correct the image URL using the `asset()` helper
//                 $team->image_url = asset('storage/team_images/' . $team->image_url);
//             }
//         }
    
//         return response()->json($teams); // Return as JSON with correctly formatted URLs
//     }

//     // Store a new team
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'role' => 'required|string|max:255',
//             'description' => 'nullable|string',
//             'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000', // Increase max to 5MB (5000KB)
//             'status' => 'required|in:Active,Archived',
//         ]);
    
//         // Handle image upload
//         if ($request->hasFile('image_url')) {
//             $image = $request->file('image_url');
//             $imageName = time() . '.' . $image->getClientOriginalExtension(); // Generate unique image name
//             $image->move(public_path('storage/team_images'), $imageName); // Move image to storage folder
//         } else {
//             $imageName = null;  // No image uploaded
//         }
    
//         // Create the new team
//         $team = Team::create([
//             'name' => $request->name,
//             'role' => $request->role,
//             'description' => $request->description,
//             'image_url' => $imageName,  // Store the image name or null if no image
//             'status' => $request->status,
//         ]);
    
//         // Ensure the image URL is formatted correctly in the response
//         if ($team->image_url) {
//             $team->image_url = asset('storage/team_images/' . $team->image_url);
//         }
    
//         return response()->json(['message' => 'Team member added successfully', 'team' => $team], 201);
//     }

//     // Update an existing team member
//     public function update(Request $request, Team $team)
//     {
//         // Validate the request
//         $validatedData = $request->validate([
//             'name' => 'nullable|string|max:255',
//             'role' => 'nullable|string|max:255',
//             'description' => 'nullable|string',
//             'status' => 'nullable|in:Active,Archived',
//             'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
//         ]);
    
//         // ✅ Ensure existing values are kept if not updated
//         $team->name = $request->input('name', $team->name);
//         $team->role = $request->input('role', $team->role);
//         $team->description = $request->input('description', $team->description);
//         $team->status = $request->input('status', $team->status);
    
//         // ✅ Handle Image Upload
//         if ($request->hasFile('image_url')) {
//             // Delete old image if it exists
//             if ($team->image_url && file_exists(public_path('storage/team_images/' . $team->image_url))) {
//                 unlink(public_path('storage/team_images/' . $team->image_url));
//             }
    
//             $image = $request->file('image_url');
//             $imageName = time() . '.' . $image->getClientOriginalExtension();
//             $image->move(public_path('storage/team_images'), $imageName);
//             $team->image_url = $imageName;
//         }
    
//         // ✅ Save the updated team
//         $team->save();
    
//         // Ensure the image URL is formatted correctly in the response
//         if ($team->image_url) {
//             $team->image_url = asset('storage/team_images/' . $team->image_url);
//         }
    
//         return response()->json(['message' => 'Team member updated successfully', 'team' => $team], 200);
//     }

//     // Delete a team member
//     public function destroy(Team $team)
//     {
//         // Delete the team member image if exists
//         if ($team->image_url && file_exists(public_path('storage/team_images/' . $team->image_url))) {
//             unlink(public_path('storage/team_images/' . $team->image_url));
//         }

//         $team->delete(); // Delete team member
//         return response()->json(['message' => 'Team member deleted successfully'], 200);
//     }
// }







namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{

 // Get all teams
 public function index()
 {
     $teams = Team::all()->map(function ($team) {
         if ($team->image_url && !str_starts_with($team->image_url, 'http')) {
             $team->image_url = asset("storage/team_images/" . basename($team->image_url));
         }
         return $team;
     });
 
     return response()->json($teams);
 }
 // Store a new team member
 public function store(Request $request)
 {
     $request->validate([
         'name' => 'required|string|max:255',
         'role' => 'required|string|max:255',
         'description' => 'required|string',
         'image' => 'nullable|image|max:2048',
         'status' => 'required|in:Active,Archived',
     ]);
 
     $imagePath = null;
     if ($request->hasFile('image')) {
         $path = $request->file('image')->store('team_images', 'public');
         $imagePath = asset("storage/" . $path);
     }
 
     $team = Team::create([
         'name' => $request->name,
         'role' => $request->role,
         'description' => $request->description,
         'image_url' => $imagePath ?? '',
         'status' => $request->status,
     ]);
 
     return response()->json($team, 201); // ✅ Ensure response returns the team directly
 }
 // Update a team member
//  public function update(Request $request, $id)
//  {
//      $team = Team::find($id);
//      if (!$team) {
//          return response()->json(['message' => 'Team member not found'], 404);
//      }
 
//      // ✅ Validate request
//      $request->validate([
//          'name' => 'required|string|max:255',
//          'role' => 'required|string|max:255',
//          'description' => 'required|string',
//          'image' => 'nullable|image|max:2048',
//          'status' => 'required|in:Active,Archived',
//      ]);
 
//      // ✅ Update team member
//      $team->name = $request->name;
//      $team->role = $request->role;
//      $team->description = $request->description;
//      $team->status = $request->status;
 
//      // ✅ Only update image if a new one is uploaded
//      if ($request->hasFile('image')) {
//          $imagePath = $request->file('image')->store('team_images', 'public');
//          $team->image_url = "/storage/team_images/" . basename($imagePath);
//      }
 
//      $team->save();
 
//      return response()->json(['message' => 'Team member updated successfully!', 'team' => $team]);
//  }


public function update(Request $request, $id)
{
    $team = Team::find($id);

    if (!$team) {
        return response()->json(['message' => 'Team member not found'], 404);
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'role' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|max:2048',
        'status' => 'required|in:Active,Archived',
    ]);

    $team->name = $request->name;
    $team->role = $request->role;
    $team->description = $request->description;
    $team->status = $request->status;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('team_images', 'public');
        $team->image_url = asset("storage/team_images/" . basename($imagePath));
    } else if ($request->has('image_url')) {
        $team->image_url = $request->image_url;
    }

    $team->save();

    return response()->json($team);
}



 // Archive a team member
 public function archive($id)
 {
     $team = Team::find($id);
 
     if (!$team) {
         return response()->json(['error' => 'Team member not found'], 404);
     }
 
     $team->status = 'Archived';
     $team->save();
 
     return response()->json([
         'message' => 'Team member archived successfully!',
         'team' => $team
     ]);
 }

 // Unarchive a team member
 public function unarchive($id)
 {
     $team = Team::find($id);
 
     if (!$team) {
         return response()->json(['error' => 'Team member not found'], 404);
     }
 
     $team->status = 'Active';
     $team->save();
 
     return response()->json([
         'message' => 'Team member restored successfully!',
         'team' => $team
     ]);
 }

 

 

    }




