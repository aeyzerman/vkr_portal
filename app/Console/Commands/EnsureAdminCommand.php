<?php

namespace App\Console\Commands;

use App\Services\AdminProvisioner;
use Illuminate\Console\Command;

class EnsureAdminCommand extends Command
{
    protected $signature = 'portal:ensure-admin';

    protected $description = 'Создать или обновить администратора из переменных ADMIN_* в .env';

    public function handle(AdminProvisioner $provisioner): int
    {
        $result = $provisioner->ensure();

        match ($result['status']) {
            'skipped' => $this->warn($result['message']),
            'created' => $this->info($result['message']),
            'updated' => $this->line($result['message']),
        };

        return self::SUCCESS;
    }
}
