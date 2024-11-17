<?php



namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user(); // Get the authenticated user
        return view('profile.show', compact('user')); // Return the profile view with user data
    }

    /**
     * Update the user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048', // Optional image upload validation
        ]);
    
        $user = Auth::user(); // Get the authenticated user
    
        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save(); // Save user changes
    
        // Update profile information if it exists
        $profile = $user->profile;
    
        if ($profile) {
            // Handle image upload if a new file is provided
            if ($request->hasFile('image')) {
                // Delete old image if it exists (optional)
                if ($profile->image) {
                    \Storage::delete($profile->image); // Delete old image from storage
                }
    
                // Store new image and save its path in the profile
                $path = $request->file('image')->store('profiles', 'public');
                $profile->image = $path; // Save new image path to profile
            }
    
            // Update other profile fields
            $profile->mobile = $request->mobile;
            $profile->address = $request->address;
            $profile->bio = $request->bio;
            $profile->save(); // Save profile changes
        }
    
        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.'); // Redirect back with success message
    }
}