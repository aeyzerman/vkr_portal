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

        $adminName = (string) config('portal.admin.name', 'Administrator');
        [$lastName, $firstName, $patronymic] = $this->splitAdminName($adminName);

        $user->fill([
            'last_name' => $lastName,
            'first_name' => $firstName,
            'patronymic' => $patronymic,
            'password' => (string) config('portal.admin.password'),
            'permissions' => User::PERM_ADMIN,
        ]);

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

    /**
     * @return array{0: string, 1: string, 2: ?string}
     */
    private function splitAdminName(string $source): array
    {
        $parts = preg_split('/\s+/u', trim($source)) ?: [];

        return [
            $parts[0] ?? 'Administrator',
            $parts[1] ?? '',
            isset($parts[2]) ? implode(' ', array_slice($parts, 2)) : null,
        ];
    }
}
