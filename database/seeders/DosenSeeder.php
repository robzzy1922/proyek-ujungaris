<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = [
            [
                'nama_dosen' => 'Kuwu 1',
                'nip' => '12345',
                'email' => 'dosen1@example.com',
                'password' => Hash::make('12345'),
                'no_hp' => '081234567890',
                'prodi' => 'Teknik Informatika',
            ],
        ];

        foreach ($data as $dosen) {
            Dosen::firstOrCreate(['nip' => $dosen['nip']], $dosen);
        }
    }
}
