<?php

namespace App\Http\Controllers;

use App\Enums\ModerationStatus;
use App\Jobs\ModerateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        
        return Inertia::render('Profile/Edit', [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->pending_name ?? $user->name,
                'approved_name' => $user->name,
                'moderation_status' => $user->moderation_status,
                'email' => $user->email,
                'phone' => $user->phone,
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        
        $nameChanged = $validated['name'] !== $user->name;

        $profileData = [
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($nameChanged) {
            $profileData['pending_name'] = $validated['name'];
            $profileData['moderation_status'] = ModerationStatus::PendingModeration;
            $profileData['moderation_reason'] = null;
            $profileData['moderated_at'] = null;
        }

        $user->update($profileData);

        if ($nameChanged) {
            ModerateProfile::dispatch($user->id);
        }

        return redirect()->back()->with(
            'success',
            $nameChanged
                ? 'Профиль обновлён. Новое имя отправлено на модерацию'
                : 'Профиль обновлён'
        );
    }
}
