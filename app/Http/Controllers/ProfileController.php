<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Manages user profile-related logic like updating of profile pictures and names.
 */
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showTestPage()
    {
        return view('edit-profile-test');
    }
    
    /** 
     * Finds a list of users whose full name matches thes search query, 
     * excluding the currently logged-in user.
     */ 
    public function searchProfile(Request $request) {
        $name = $request->input('name');

        // Check if additional 'notInRoom' filter is requested
        if ($request->filled('notInRoom')) {
            $roomId = $request->input('notInRoom');

            // Only get users not in the roomId
            return User::whereDoesntHave('rooms', function (Builder $query) use ($roomId) {
                $query->where('members.room_id', '=', $roomId);
            })->where('id', '!=', auth()->user()->id)
            ->where(function ($query) use ($name) {
                $query->where('first_name', 'like', '%' . $name . '%');
                $query->orWhere('last_name', 'like', '%' . $name . '%');
            })
            ->limit(15)
            ->get();
        }
        else {
            return User::where('id', '!=', auth()->user()->id)
                ->where(function ($query) use ($name) {
                    $query->where('first_name', 'like', '%' . $name . '%');
                    $query->orWhere('last_name', 'like', '%' . $name . '%');
                })
                ->limit(15)
                ->get();
        }
    }

    // Retrieves the current user's profile
    public function getProfile() {
        $currentUser = auth()->user();

        return [
            'firstName' => $currentUser->first_name,
            'lastName' => $currentUser->last_name,
            'profilePicture' => $currentUser->profile_picture
        ];
    }

    // Endpoint to update a certain user's name and profile picture
    public function updateProfile(Request $request)
    {
        // Validate input before saving changes to the database
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'profilePicture' => 'image|mimes:jpeg,jpg,png|max:4096'
        ]);
        
        // If the request does not pass validation, inform the user
        if ($validator->fails()) {
            return response($validator->errors(), 400); 
        }

        // Retrieve input data from the request
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        
        // Update the user's record in the database
        $user = auth()->user();
        $user->first_name = $firstName;
        $user->last_name = $lastName;

        // Check if request includes a profile picture
        if ($request->hasFile('profilePicture')) {
            $profilePicturePath = $request->file('profilePicture')->store('images', 'public');
            $user->profile_picture = $profilePicturePath;
        }

        $user->save();
        
        // Echo back the request as a successful response
        return [
            "id" => $user->id,
            "firstName" => $user->first_name,
            "lastName" => $user->last_name,
            "profilePicture" => $user->profile_picture
        ];
    }

}
