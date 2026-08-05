<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@sistemaconsumo.mx'],
            ['name' => 'Usuario Demo', 'password' => Hash::make('Demo1234!')]
        );

        $demo = [
            ['name' => 'Refrigerador', 'watts' => 350, 'volts' => 127, 'always_plugged' => true, 'standby_watts' => 2],
            ['name' => 'Televisión sala', 'watts' => 120, 'volts' => 127, 'always_plugged' => true, 'standby_watts' => 1.5],
            ['name' => 'Laptop', 'watts' => 65, 'volts' => 127, 'always_plugged' => false, 'standby_watts' => 0],
            ['name' => 'Lavadora', 'watts' => 500, 'volts' => 127, 'always_plugged' => true, 'standby_watts' => 1],
        ];

        foreach ($demo as $d) {
            Device::firstOrCreate(
                ['user_id' => $user->id, 'name' => $d['name']],
                $d + ['user_id' => $user->id]
            );
        }
    }
}
