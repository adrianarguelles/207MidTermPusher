<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

/**
 * Manages user profile-related logic like updating of profile pictures and names.
 */
class ProfileController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    // Endpoint to update a certain user's name and profile picture
    public function update(Request $request, $id)
    {
        // TODO: Validate input before saving changes to the database
        // $request->validate([
        //     'firstname' => 'required',
        //     'lastname' => 'required',
        //     'profilepicture' => 'image|mimes:jpeg,jpg,png|max:2048'
        // ]);

        // Retrieve input data from the request
        $firstName = $request->input('firstname');
        $lastName = $request->input('lastname');
        $profilePicture = $request->file('profilepicture');
        
        // Update the user's record in the database
        $user = User::findOrFail($id);
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        // $user->profile_picture = $profilePicture;

        $user->save();
        
        // Respond successfully
        return [
            "id" => $id,
            "firstName" => $user->first_name,
            "lastName" => $user->last_name,
            "profilePictureExtension" => (!is_null($profilePicture)) ? $profilePicture->extension() : ""
        ];
    }

}
