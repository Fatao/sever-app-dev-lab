<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Messenger;
use Illuminate\Database\Seeder;

class MessengerSeeder extends Seeder
{
    public function run(): void
    {
        Messenger::firstOrCreate(['name' => 'telegram'], [
            'description' => 'Telegram messenger integration',
            'environment' => 'local',
            'token_env_var' => 'MESSENGER_TELEGRAM_TOKEN',
        ]);
    }
}