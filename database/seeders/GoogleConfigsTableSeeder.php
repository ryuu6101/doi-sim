<?php

namespace Database\Seeders;

use App\Models\GoogleConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GoogleConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'scopes' => json_encode(['https://www.googleapis.com/auth/spreadsheets']),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        GoogleConfig::truncate();
        GoogleConfig::insert($data);
    }
}
