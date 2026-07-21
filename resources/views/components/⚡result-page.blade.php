<?php

use App\Services\InterpretationService;
use Livewire\Component;

new class extends Component {
    public \App\Models\Submission $submission;

    public function mount(\App\Models\Submission $submission): void
    {
        // Eager load questions.options supaya bisa hitung skor maksimal per trait
        // untuk bar chart (MBTI/DISC/Big Five), tanpa N+1 query.
        $this->submission = $submission->load('quiz.questions.options');
    }

    public function interpretation(): string
    {
        return app(InterpretationService::class)->interpret($this->submission);
    }

    /**
     * Mode hasil: null (skor biasa), 'dichotomy_code' (MBTI),
     * 'dominant_trait' (DISC), atau 'trait_profile' (Big Five).
     */
    public function resultMode(): ?string
    {
        $ranges = $this->submission->quiz->interpretation_ranges ?? [];
        return is_array($ranges) ? ($ranges['result_mode'] ?? null) : null;
    }

    public function summary(): array
    {
        return is_array($this->submission->result_summary) ? $this->submission->result_summary : [];
    }

    /**
     * Skor maksimal per trait_key, dihitung dari nilai opsi tertinggi
     * di tiap pertanyaan yang berelasi ke trait itu. Dipakai untuk
     * menentukan lebar bar chart secara proporsional.
     */
    public function traitMaxScores(): array
    {
        $max = [];
        foreach ($this->submission->quiz->questions as $question) {
            foreach ($question->options->groupBy('trait_key') as $traitKey => $options) {
                if (! $traitKey) {
                    continue;
                }
                $max[$traitKey] = ($max[$traitKey] ?? 0) + $options->max('value');
            }
        }
        return $max;
    }

    public function barWidth(int $score, string $traitKey): int
    {
        $max = $this->traitMaxScores();
        $denom = $max[$traitKey] ?? 0;
        return $denom > 0 ? (int) round(($score / $denom) * 100) : 0;
    }
};
?>

<section class="result-panel">
    <p class="eyebrow">ASSESSMENT COMPLETE</p>
    <h1>Terima kasih, {{ $submission->participant_name ?: 'teman' }}.</h1>

    @php($mode = $this->resultMode())
    @php($summary = $this->summary())

    @if ($mode === 'dichotomy_code')
        {{-- ===== MBTI ===== --}}
        <div class="mbti-code" style="text-align:center; margin: 24px 0;">
            <span style="font-size: 3rem; font-weight: 800; letter-spacing: 0.1em;">{{ $summary['code'] ?? '-' }}</span>
        </div>
        <h2 style="text-align:center;">{{ $this->interpretation() }}</h2>

        <div class="dichotomy-breakdown" style="margin-top: 24px; display: flex; flex-direction: column; gap: 16px;">
            @foreach ($summary['breakdown'] ?? [] as $pair)
                @php($scores = $pair['scores'])
                @php($total = array_sum($scores) ?: 1)
                @php($traitA = $pair['pair'][0])
                @php($traitB = $pair['pair'][1])
                <div>
                    <div style="display:flex; justify-content:space-between; font-size: 0.85rem; margin-bottom: 4px;">
                        <span>{{ $pair['labels'][$traitA] }} ({{ $traitA }})</span>
                        <span>{{ $pair['labels'][$traitB] }} ({{ $traitB }})</span>
                    </div>
                    <div style="display:flex; height: 10px; border-radius: 6px; overflow: hidden; background:#e5e7eb;">
                        <div style="width: {{ round(($scores[$traitA] / $total) * 100) }}%; background:#0f766e;"></div>
                        <div style="width: {{ round(($scores[$traitB] / $total) * 100) }}%; background:#f59e0b;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size: 0.75rem; color:#6b7280; margin-top:2px;">
                        <span>{{ $scores[$traitA] }}</span>
                        <span>{{ $scores[$traitB] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

    @elseif ($mode === 'dominant_trait')
        {{-- ===== DISC ===== --}}
        <div class="score-ring">
            <strong>{{ $summary['dominant_trait'] ?? '-' }}</strong>
            <span>gaya dominan</span>
        </div>
        <h2>{{ $this->interpretation() }}</h2>

        <div class="trait-profile" style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
            @foreach ($summary['trait_scores'] ?? [] as $traitKey => $score)
                <div>
                    <div style="display:flex; justify-content:space-between; font-size: 0.85rem; margin-bottom: 4px;">
                        <span>{{ $summary['trait_meta'][$traitKey] ?? $traitKey }}</span>
                        <span>{{ $score }}</span>
                    </div>
                    <div style="height: 10px; border-radius: 6px; background:#e5e7eb;">
                        <div style="width: {{ $this->barWidth($score, $traitKey) }}%; height:100%; border-radius:6px; background:#0f766e;"></div>
                    </div>
                </div>
            @endforeach
        </div>

    @elseif ($mode === 'trait_profile')
        {{-- ===== Big Five ===== --}}
        <h2 style="text-align:center;">{{ $this->interpretation() }}</h2>

        <div class="trait-profile" style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
            @foreach ($summary['trait_profile'] ?? [] as $trait)
                <div>
                    <div style="display:flex; justify-content:space-between; font-size: 0.85rem; margin-bottom: 4px;">
                        <span>{{ $trait['label'] }}</span>
                        <span>{{ $trait['score'] }}</span>
                    </div>
                    <div style="height: 10px; border-radius: 6px; background:#e5e7eb;">
                        <div style="width: {{ $this->barWidth($trait['score'], $trait['trait_key']) }}%; height:100%; border-radius:6px; background:#0f766e;"></div>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        {{-- ===== Quiz pengetahuan & assessment Stres/Kecemasan (skor + label) ===== --}}
        <div class="score-ring">
            <strong>{{ $submission->quiz->type === 'assessment' ? $submission->score : $submission->percentage.'%' }}</strong>
            <span>{{ $submission->quiz->type === 'assessment' ? 'skor refleksi' : 'skor' }}</span>
        </div>
        <h2>{{ $this->interpretation() }}</h2>
        @if (! empty($summary['description']))
            <p>{{ $summary['description'] }}</p>
        @endif
    @endif

    <p style="margin-top: 24px;">Gunakan hasil ini sebagai titik awal untuk memahami kondisi Anda. Untuk dukungan profesional, hubungi psikolog atau layanan kesehatan mental tepercaya.</p>
    <a class="button" href="/">Kembali ke beranda</a>
</section>