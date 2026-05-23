<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class AdminProvisioner
{
    public function isConfigured(): bool
    {
        return filled(config('portal.admin.email'))
            && filled(config('portal.admin.password'));
    }

    /**
     * @return array{status: 'skipped'|'created'|'updated', message: string, user?: User}
     */
    public function ensure(): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'skipped',
                'message' => 'Задайте ADMIN_EMAIL и ADMIN_PASSWORD в .env',
            ];
        }

        $email = Str::lower((string) config('portal.admin.email'));
        $user = User::query()->firstOrNew(['email' => $email]);

        $created = ! $user->exists;

        $user->fill([
            'name' => (string) config('portal.admin.name', 'Administrator'),
            'password' => (string) config('portal.admin.password'),
            'permissions' => User::PERM_ADMIN,
        ]);

        if ($user->full_name === null) {
            $user->full_name = $user->name;
        }

        $user->email_verified_at ??= now();
        $user->save();

        return [
            'status' => $created ? 'created' : 'updated',
            'message' => $created
                ? "Создан администратор: {$email}"
                : "Обновлён администратор: {$email}",
            'user' => $user,
        ];
    }
}
