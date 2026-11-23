<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        foreach (['PEMILIK', 'KASIR'] as $name) {
            DB::table($tableNames['roles'])->updateOrInsert(
                ['name' => $name, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }

        $roleIds = DB::table($tableNames['roles'])
            ->where('guard_name', 'web')
            ->whereIn('name', ['PEMILIK', 'KASIR'])
            ->pluck('id', 'name');

        DB::table('users')
            ->orderBy('id')
            ->eachById(function (object $user) use ($tableNames, $roleIds): void {
                $role = in_array($user->role, ['PEMILIK', 'KASIR'], true)
                    ? $user->role
                    : 'KASIR';

                DB::table($tableNames['model_has_roles'])->insertOrIgnore([
                    'role_id' => $roleIds[$role],
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('KASIR');
        });

        DB::table($tableNames['model_has_roles'].' as user_roles')
            ->join($tableNames['roles'].' as roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.model_type', User::class)
            ->orderBy('user_roles.model_id')
            ->get(['user_roles.model_id', 'roles.name'])
            ->each(function (object $userRole): void {
                DB::table('users')
                    ->where('id', $userRole->model_id)
                    ->update(['role' => $userRole->name]);
            });
    }
};
