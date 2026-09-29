<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingController extends Controller
{
    public const RULES = [
        'site_name' => 'required|string|max:100',
        'support_email' => 'required|email|max:255',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'announcement' => 'nullable|string|max:500',
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'site_name' => Setting::get('site_name', 'Jelajahi'),
                'support_email' => Setting::get('support_email', ''),
                'tax_rate' => Setting::get('tax_rate', '10'),
                'announcement' => Setting::get('announcement', ''),
            ],
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(self::RULES);

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
        ]);

        if ($data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update(['password' => Hash::make($request->input('password'))]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Password berhasil diganti.');
    }
}
