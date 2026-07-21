<?php

namespace App\Services;

use App\Models\Submission;

class InterpretationService
{
    /**
     * Get the interpretation headline for a submission.
     *
     * Untuk assessment trait-based (MBTI/DISC/Big Five), ScoringService sudah
     * menghasilkan pesan yang tepat (kode 4 huruf / gaya dominan / profil) dan
     * disimpan di result_summary['message'] saat submit — jadi cukup dipakai
     * langsung di sini tanpa mencoba mencocokkan ke range min/max.
     */
    public function interpret(Submission $submission): string
    {
        $submission->loadMissing('quiz');

        if (! empty($submission->result_summary['message'])) {
            return $submission->result_summary['message'];
        }

        // Fallback lama: cocokkan skor ke rentang min/max (hanya berlaku untuk
        // assessment berbasis skor seperti Stres/Kecemasan). Item yang bukan
        // array range valid (mis. metadata trait-based) dilewati dengan aman.
        $ranges = $submission->quiz->interpretation_ranges ?? [];
        foreach ($ranges as $range) {
            if (! is_array($range) || ! isset($range['min'], $range['max'])) {
                continue;
            }
            if ($submission->score !== null && $submission->score >= $range['min'] && $submission->score <= $range['max']) {
                return $range['label'] ?? 'Hasil refleksi Anda';
            }
        }

        if ($submission->quiz->type === 'quiz') {
            $passingScore = $submission->quiz->passing_score ?? 70;
            return $submission->percentage >= $passingScore ? 'Lulus' : 'Belum lulus';
        }

        return 'Hasil refleksi Anda';
    }

    /**
     * Deskripsi tambahan untuk label lama (Stres/Kecemasan/Quiz).
     * Untuk assessment trait-based, deskripsi sudah tersedia langsung
     * di komponen hasil (result-page) lewat data breakdown/trait_profile,
     * jadi method ini tidak dipakai untuk mode tersebut.
     */
    public function getDescription(string $label): string
    {
        return match ($label) {
            'Perlu perhatian lebih' => 'Anda mungkin mengalami tekanan yang cukup berarti. Pertimbangkan untuk mencari dukungan.',
            'Cukup terkelola' => 'Anda mampu mengelola tekanan dengan cukup baik, namun tetap perlu menjaga keseimbangan.',
            'Dalam kondisi baik' => 'Anda berada dalam kondisi yang baik. Terus jaga pola hidup sehat.',
            'Gejala kecemasan minimal' => 'Gejala kecemasan yang Anda alami tergolong ringan atau jarang muncul.',
            'Gejala kecemasan sedang' => 'Ada gejala kecemasan yang cukup terasa. Perhatikan pemicunya dan pertimbangkan strategi relaksasi.',
            'Gejala kecemasan tinggi' => 'Gejala kecemasan cukup mengganggu. Disarankan berkonsultasi dengan profesional kesehatan mental.',
            'Lulus' => 'Selamat! Anda telah mencapai skor minimal yang ditentukan.',
            'Belum lulus' => 'Anda belum mencapai skor minimal. Silakan coba lagi.',
            default => 'Gunakan hasil ini sebagai titik awal untuk memahami kondisi Anda.',
        };
    }
}