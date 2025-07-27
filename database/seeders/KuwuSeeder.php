<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Kuwu;
use Illuminate\Support\Facades\Hash;

class KuwuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = [
            [
                'nama_kuwu' => 'Tegar Wira Kesuma',
                'nip' => '12345',
                'email' => 'kuwu1@example.com',
                'password' => Hash::make('12345'),
                'no_hp' => '081234567890',
            ],
        ];

        foreach ($data as $kuwu) {
            Kuwu::firstOrCreate(['nip' => $kuwu['nip']], $kuwu);
        }
    }
}
