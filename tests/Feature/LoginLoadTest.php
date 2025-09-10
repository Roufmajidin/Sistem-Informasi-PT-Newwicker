<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class LoginLoadTestCommand extends Command
{
    protected $signature = 'test:login {count=50}';
    protected $description = 'Load test login API dengan N request';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $success = 0;

        $this->info("Menjalankan {$count} request login...");

        for ($i = 1; $i <= $count; $i++) {
            $response = Http::post('http://127.0.0.1:8000/api/login', [
                'email'    => 'test@example.com',
                'password' => 'password123',
            ]);

            if ($response->ok()) {
                $success++;
                $this->line("✅ login user {$i} berhasil");
            } else {
                $this->warn("❌ login user {$i} gagal (status: {$response->status()})");
            }
        }

        $this->info("Selesai. {$success}/{$count} login berhasil.");
    }
}
