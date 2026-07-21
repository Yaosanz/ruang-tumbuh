<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Support\Collection;

class ScoringService
{
    public function calculate(Quiz $quiz, Collection $selectedOptions): array
    {
        $ranges = $quiz->interpretation_ranges ?? [];
        $resultMode = is_array($ranges) ? ($ranges['result_mode'] ?? null) : null;

        return match ($resultMode) {
            'dichotomy_code' => $this->calculateDichotomy($quiz, $selectedOptions, $ranges),
            'dominant_trait' => $this->calculateDominantTrait($quiz, $selectedOptions, $ranges),
            'trait_profile' => $this->calculateTraitProfile($quiz, $selectedOptions, $ranges),
            default => $this->calculateScoreBased($quiz, $selectedOptions, $ranges),
        };
    }

    /**
     * Mode lama/default: skor total dijumlah lalu dicocokkan ke rentang min/max.
     * Dipakai untuk quiz pengetahuan (benar/salah) dan assessment Stres/Kecemasan
     * (skor Likert + rentang interpretasi berupa array [min, max, label]).
     */
    private function calculateScoreBased(Quiz $quiz, Collection $selectedOptions, array $ranges): array
    {
        $score = 0;
        $maxScore = 0;

        foreach ($quiz->questions as $question) {
            $option = $selectedOptions->get($question->id);
            $weight = $question->points;

            $maxScore += $quiz->type === 'assessment'
                ? ((int) $question->options->max('value') * $weight)
                : $weight;

            if (! $option) {
                continue;
            }

            if ($quiz->type === 'assessment') {
                $score += $option->value * $weight;
            } elseif ($option->is_correct) {
                $score += $weight;
            }
        }

        $percentage = $maxScore ? (int) round(($score / $maxScore) * 100) : 0;
        $matched = $this->findRange($ranges, $score);

        $summary = [
            'message' => $matched['label'] ?? $this->fallbackMessage($quiz, $percentage),
        ];

        if ($matched) {
            $summary['label'] = $matched['label'] ?? null;
            $summary['description'] = $matched['description'] ?? null;
        }

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'summary' => $summary,
        ];
    }

    /**
     * MBTI: akumulasi skor per trait_key, lalu untuk tiap pasangan dikotomi
     * (E-I, S-N, T-F, J-P) tentukan huruf dengan skor lebih tinggi, gabung
     * jadi kode 4 huruf (mis. "INFJ").
     */
    private function calculateDichotomy(Quiz $quiz, Collection $selectedOptions, array $ranges): array
    {
        $traitScores = $this->accumulateTraitScores($quiz, $selectedOptions);
        $pairs = $ranges['dichotomy_pairs'] ?? [];
        $traitMeta = $ranges['trait_meta'] ?? [];

        $code = '';
        $breakdown = [];

        foreach ($pairs as $pair) {
            [$traitA, $traitB] = $pair;
            $scoreA = $traitScores[$traitA] ?? 0;
            $scoreB = $traitScores[$traitB] ?? 0;
            $winner = $scoreA >= $scoreB ? $traitA : $traitB;
            $code .= $winner;

            $breakdown[] = [
                'pair' => [$traitA, $traitB],
                'labels' => [$traitA => $traitMeta[$traitA] ?? $traitA, $traitB => $traitMeta[$traitB] ?? $traitB],
                'scores' => [$traitA => $scoreA, $traitB => $scoreB],
                'result' => $winner,
            ];
        }

        return [
            'score' => null,
            'max_score' => null,
            'percentage' => null,
            'summary' => [
                'message' => $code !== '' ? "Tipe kepribadian Anda: {$code}" : 'Hasil refleksi Anda',
                'code' => $code,
                'trait_scores' => $traitScores,
                'breakdown' => $breakdown,
            ],
        ];
    }

    /**
     * DISC: akumulasi skor per trait, ambil 1 trait dengan skor tertinggi
     * sebagai gaya dominan. Skor semua trait tetap disimpan untuk profil.
     */
    private function calculateDominantTrait(Quiz $quiz, Collection $selectedOptions, array $ranges): array
    {
        $traitScores = $this->accumulateTraitScores($quiz, $selectedOptions);
        $traitMeta = $ranges['trait_meta'] ?? [];

        arsort($traitScores);
        $dominant = array_key_first($traitScores);

        return [
            'score' => null,
            'max_score' => null,
            'percentage' => null,
            'summary' => [
                'message' => $dominant
                    ? 'Gaya perilaku dominan Anda: '.($traitMeta[$dominant] ?? $dominant)
                    : 'Hasil refleksi Anda',
                'dominant_trait' => $dominant,
                'dominant_trait_label' => $dominant ? ($traitMeta[$dominant] ?? $dominant) : null,
                'trait_scores' => $traitScores,
                'trait_meta' => $traitMeta,
            ],
        ];
    }

    /**
     * Big Five: tampilkan profil kelima trait (O/C/E/A/N) beserta skornya
     * masing-masing, tanpa menentukan satu "pemenang" tunggal.
     */
    private function calculateTraitProfile(Quiz $quiz, Collection $selectedOptions, array $ranges): array
    {
        $traitScores = $this->accumulateTraitScores($quiz, $selectedOptions);
        $traitMeta = $ranges['trait_meta'] ?? [];

        $profile = [];
        foreach ($traitMeta as $key => $label) {
            $profile[] = [
                'trait_key' => $key,
                'label' => $label,
                'score' => $traitScores[$key] ?? 0,
            ];
        }

        return [
            'score' => null,
            'max_score' => null,
            'percentage' => null,
            'summary' => [
                'message' => 'Berikut profil kepribadian Anda berdasarkan lima dimensi utama.',
                'trait_profile' => $profile,
                'trait_scores' => $traitScores,
            ],
        ];
    }

    /**
     * Jumlahkan value opsi yang dipilih, dikelompokkan per trait_key.
     * Dipakai bersama oleh MBTI, DISC, dan Big Five.
     */
    private function accumulateTraitScores(Quiz $quiz, Collection $selectedOptions): array
    {
        $traitScores = [];

        foreach ($quiz->questions as $question) {
            $option = $selectedOptions->get($question->id);
            if (! $option || ! $option->trait_key) {
                continue;
            }
            $traitScores[$option->trait_key] = ($traitScores[$option->trait_key] ?? 0) + $option->value;
        }

        return $traitScores;
    }

    /**
     * Cari range [min, max] yang cocok dengan skor. Item yang bukan array
     * range yang valid (mis. metadata trait-based) dilewati dengan aman.
     */
    private function findRange(array $ranges, int $score): ?array
    {
        foreach ($ranges as $range) {
            if (! is_array($range) || ! isset($range['min'], $range['max'])) {
                continue;
            }
            if ($score >= $range['min'] && $score <= $range['max']) {
                return $range;
            }
        }

        return null;
    }

    private function fallbackMessage(Quiz $quiz, int $percentage): string
    {
        return $quiz->type === 'quiz'
            ? ($quiz->passing_score !== null && $percentage >= $quiz->passing_score ? 'Lulus' : 'Belum lulus')
            : 'Hasil refleksi Anda';
    }
}