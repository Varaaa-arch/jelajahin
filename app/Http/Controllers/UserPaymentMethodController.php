<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPaymentMethodController extends Controller
{
    /**
     * Sumber kebenaran user SELALU dari session auth, bukan dari client.
     * Jika client mengirim user_id dan tidak cocok → 403.
     */
    private function authorizeUser(Request $request): string
    {
        $authId = (string) $request->user()->id;

        if ($request->filled('user_id') && (string) $request->input('user_id') !== $authId) {
            abort(403, 'Akses ditolak.');
        }

        return $authId;
    }

    private function serialize(UserPaymentMethod $m): array
    {
        return [
            'id' => $m->id,
            'type' => $m->type,
            'provider' => $m->provider,
            'label' => $m->label,
            'account_name' => $m->account_name,
            'masked_number' => $m->maskedNumber(),
            'expiry' => $m->expiry,
            'is_default' => (bool) $m->is_default,
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $this->authorizeUser($request);

        $methods = UserPaymentMethod::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->serialize($m))
            ->values();

        return response()->json(['data' => $methods]);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = $this->authorizeUser($request);
        $data = $this->validatePayload($request);

        // Kartu: hanya simpan 4 digit terakhir, JANGAN PERNAH simpan PAN penuh
        if ($data['type'] === 'card') {
            $data['account_number'] = substr(preg_replace('/\D/', '', $data['account_number']), -4);
        }

        $method = UserPaymentMethod::create(array_merge($data, ['user_id' => $userId]));

        if ($request->boolean('is_default')) {
            $this->markDefault($method);
        }

        return response()->json([
            'message' => 'Metode pembayaran tersimpan.',
            'data' => $this->serialize($method->fresh()),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $userId = $this->authorizeUser($request);
        $method = UserPaymentMethod::where('id', $id)->where('user_id', $userId)->firstOrFail();

        $data = $this->validatePayload($request, partial: true);

        $effectiveType = $data['type'] ?? $method->type;
        if (isset($data['account_number']) && $effectiveType === 'card') {
            $data['account_number'] = substr(preg_replace('/\D/', '', $data['account_number']), -4);
        }

        $method->update($data);

        if ($request->boolean('is_default')) {
            $this->markDefault($method);
        }

        return response()->json([
            'message' => 'Metode pembayaran diperbarui.',
            'data' => $this->serialize($method->fresh()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $userId = $this->authorizeUser($request);
        $method = UserPaymentMethod::where('id', $id)->where('user_id', $userId)->firstOrFail();

        $wasDefault = $method->is_default;
        $method->delete();

        // Jika yang dihapus adalah default, promosikan metode tertua jadi default
        if ($wasDefault) {
            $next = UserPaymentMethod::where('user_id', $userId)->orderBy('created_at')->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Metode pembayaran dihapus.']);
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        $userId = $this->authorizeUser($request);
        $method = UserPaymentMethod::where('id', $id)->where('user_id', $userId)->firstOrFail();

        $this->markDefault($method);

        return response()->json([
            'message' => 'Metode utama diperbarui.',
            'data' => $this->serialize($method->fresh()),
        ]);
    }

    private function markDefault(UserPaymentMethod $method): void
    {
        UserPaymentMethod::where('user_id', $method->user_id)
            ->where('id', '!=', $method->id)
            ->update(['is_default' => false]);

        $method->update(['is_default' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, bool $partial = false): array
    {
        $sometimes = $partial ? 'sometimes|' : '';

        $type = $request->input('type');
        if (! $type && $partial) {
            $type = UserPaymentMethod::where('id', $request->route('id'))
                ->where('user_id', $request->user()->id)
                ->value('type');
        }

        $providers = $type && isset(UserPaymentMethod::PROVIDERS[$type])
            ? UserPaymentMethod::PROVIDERS[$type]
            : array_merge(...array_values(UserPaymentMethod::PROVIDERS));

        $rules = [
            'type' => [$sometimes . 'required', Rule::in(UserPaymentMethod::TYPES)],
            'provider' => [$sometimes . 'required', Rule::in($providers)],
            'label' => [$sometimes . 'nullable', 'string', 'max:100'],
            'account_name' => [$sometimes . 'required', 'string', 'max:100'],
            'account_number' => [$sometimes . 'required', 'string', 'max:32'],
            'expiry' => [$sometimes . 'nullable', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
        ];

        // e-wallet: nomor HP / akun 9-16 digit
        if ($type === 'e_wallet') {
            $rules['account_number'][] = 'regex:/^[0-9+]{9,16}$/';
        }

        return $request->validate($rules);
    }
}
