<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        // Validate input before saving changes to the database
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'profilepicture' => 'image|mimes:jpeg,jpg,png|max:4096'
        ]);
        
        // If the request does not pass validation, inform the user
        if ($validator->fails()) {
            return response($validator->errors(), 400); 
        }

        // Retrieve input data from the request
        $firstName = $request->input('firstname');
        $lastName = $request->input('lastname');
        $profilePicture = $request->file('profilepicture');
        
        // Update the user's record in the database
        $user = User::findOrFail($id);
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->profile_picture = $profilePicture;
        // TODO: Allow updating of profile picture

        $user->save();
        
        // Echo back the request as a successful response
        return [
            "id" => $id,
            "firstName" => $user->first_name,
            "lastName" => $user->last_name,
            "profilePicture" => (!is_null($profilePicture)) ? $profilePicture->extension() : ""
        ];
    }

}
