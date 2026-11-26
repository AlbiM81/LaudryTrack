<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CashierAccountController extends Controller
{
    public function index(): JsonResponse
    {
        $cashiers = User::role('KASIR')
            ->orderBy('name')
            ->get()
            ->map(fn (User $cashier): array => $this->cashierPayload($cashier));

        return response()->json(['data' => $cashiers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $cashier = DB::transaction(function () use ($validated): User {
            $cashier = User::create([
                ...$validated,
                'is_active' => true,
            ]);
            Role::findOrCreate('KASIR', 'web');
            $cashier->assignRole('KASIR');

            return $cashier;
        });

        return response()->json([
            'message' => 'Akun Kasir berhasil dibuat.',
            'data' => $this->cashierPayload($cashier),
        ], 201);
    }

    public function show(User $cashier): JsonResponse
    {
        $this->ensureCashier($cashier);

        return response()->json(['data' => $this->cashierPayload($cashier)]);
    }

    public function update(Request $request, User $cashier): JsonResponse
    {
        $this->ensureCashier($cashier);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'alpha_dash',
                'max:100',
                Rule::unique('users', 'username')->ignore($cashier->id),
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($cashier->id),
            ],
            'password' => ['sometimes', 'required', 'string', Password::min(8), 'confirmed'],
        ]);

        $cashier->update($validated);

        return response()->json([
            'message' => 'Akun Kasir berhasil diperbarui.',
            'data' => $this->cashierPayload($cashier->refresh()),
        ]);
    }

    public function destroy(User $cashier): JsonResponse
    {
        $this->ensureCashier($cashier);

        DB::transaction(function () use ($cashier): void {
            $cashier->update(['is_active' => false]);
            $cashier->tokens()->delete();
        });

        return response()->json([
            'message' => 'Akun Kasir berhasil dinonaktifkan.',
            'data' => $this->cashierPayload($cashier->refresh()),
        ]);
    }

    private function ensureCashier(User $user): void
    {
        abort_unless($user->hasRole('KASIR'), 404);
    }

    /**
     * @return array{id: int, name: string, username: string, email: string, is_active: bool}
     */
    private function cashierPayload(User $cashier): array
    {
        return [
            'id' => $cashier->id,
            'name' => $cashier->name,
            'username' => $cashier->username,
            'email' => $cashier->email,
            'is_active' => $cashier->is_active,
        ];
    }
}
