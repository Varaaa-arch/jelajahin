<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public const ROLES = ['user', 'admin'];
    public const STATUSES = ['active', 'inactive', 'suspended'];

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        $role = $request->input('role', 'all');
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'recent');
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;

        $query = User::query()->withCount('bookings');

        if ($q !== '') {
            // LOWER()+LIKE agar case-insensitive di pgsql, mysql, maupun sqlite.
            $like = '%'.mb_strtolower($q).'%';
            $query->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
            });
        }

        if ($role !== 'all' && in_array($role, self::ROLES, true)) {
            $query->where('role', $role);
        }

        if ($status !== 'all' && in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        }

        match ($sort) {
            'oldest' => $query->orderBy('users.created_at'),
            'name_az' => $query->orderBy('name'),
            'bookings' => $query->orderByDesc('bookings_count')->orderByDesc('users.created_at'),
            default => $query->orderByDesc('users.created_at'),
        };

        $query->select('users.*');

        $paginator = $query->paginate($perPage)->withQueryString();

        $data = collect($paginator->items())->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'initial' => mb_strtoupper(mb_substr(trim($u->name), 0, 1) ?: 'U'),
            'avatar' => $u->avatar,
            'provider' => $u->provider,
            'role' => $u->role ?? 'user',
            'status' => $u->status ?? 'active',
            'verified' => $u->email_verified_at !== null,
            'bookings_count' => (int) ($u->bookings_count ?? 0),
            'joined_at' => $u->created_at?->format('Y-m-d'),
            'is_self' => $u->id === $request->user()?->id,
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => [
                'data' => $data->toArray(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => [
                'q' => $q,
                'role' => $role,
                'status' => $status,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => 'active',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        // Admin tidak boleh mengubah role/status dirinya sendiri.
        if ($user->id === $request->user()?->id && ($data['role'] !== $user->role || $data['status'] !== ($user->status ?? 'active'))) {
            return back()->withErrors(['role' => 'Anda tidak dapat mengubah role/status akun sendiri.']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => $data['password']]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password {$user->name} berhasil direset.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()?->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        if ($user->bookings()->exists()) {
            return back()->withErrors(['user' => "Pengguna {$user->name} memiliki riwayat booking dan tidak dapat dihapus. Suspend saja bila perlu."]);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}
