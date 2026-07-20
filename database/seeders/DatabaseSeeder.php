<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@ruangtumbuh.test'], ['name' => 'Administrator', 'password' => Hash::make('password'), 'is_admin' => true]);
        $quiz = Quiz::updateOrCreate(['slug' => 'cek-kondisi-stres'], ['title' => 'Cek Kondisi Stres', 'description' => 'Refleksi singkat untuk mengenali bagaimana tekanan hadir dalam keseharian Anda.', 'type' => 'assessment', 'duration_minutes' => 5, 'is_published' => true, 'interpretation_ranges' => [['min' => 0, 'max' => 6, 'label' => 'Perlu perhatian lebih'], ['min' => 7, 'max' => 12, 'label' => 'Cukup terkelola'], ['min' => 13, 'max' => 20, 'label' => 'Dalam kondisi baik']]]);
        if (! $quiz->questions()->exists()) {
            foreach (['Saya merasa kewalahan dengan tugas sehari-hari.', 'Saya dapat beristirahat dengan cukup.', 'Saya merasa tegang atau sulit rileks.', 'Saya punya dukungan saat menghadapi masalah.'] as $i => $text) {
                $question = $quiz->questions()->create(['question' => $text, 'position' => $i]);
                foreach (['Tidak pernah', 'Jarang', 'Kadang-kadang', 'Sering', 'Sangat sering'] as $j => $label) $question->options()->create(['label' => $label, 'value' => $j + 1, 'position' => $j]);
            }
        }
    }
}
