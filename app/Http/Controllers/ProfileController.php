<?php

namespace App\Http\Controllers;

use App\Enums\ProfileType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'profile_type' => ['required', Rule::enum(ProfileType::class)],
        ]);

        $user->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profile updated.');
    }
}
