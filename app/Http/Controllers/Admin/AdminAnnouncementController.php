<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AdminAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class AdminAnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::latest()->paginate(10);

        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
        ];

        return Inertia::render('Admin/Broadcast/Index', [
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'target' => 'required|in:all,user,admin',
        ]);

        $announcement = Announcement::create([
            'subject' => $data['subject'],
            'message' => $data['message'],
            'target' => $data['target'],
            'created_by' => $request->user()->id,
            'sent_count' => 0,
        ]);

        $query = User::query();
        if ($data['target'] === 'user') {
            $query->where('role', 'user');
        } elseif ($data['target'] === 'admin') {
            $query->where('role', 'admin');
        }

        $sent = 0;
        // Chunk agar tidak OOM saat user banyak; tiap notif masuk queue (ShouldQueue).
        $query->chunkById(200, function ($users) use ($announcement, &$sent) {
            Notification::send($users, new AdminAnnouncementNotification($announcement));
            $sent += $users->count();
        });

        $announcement->update(['sent_count' => $sent]);

        return back()->with('success', "Broadcast terkirim ke {$sent} user via email + notifikasi.");
    }
}
