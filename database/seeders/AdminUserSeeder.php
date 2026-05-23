<?php

namespace Database\Seeders;

use App\Services\AdminProvisioner;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(AdminProvisioner $provisioner): void
    {
        $provisioner->ensure();
    }
}
