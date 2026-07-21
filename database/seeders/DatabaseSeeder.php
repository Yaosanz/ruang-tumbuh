<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@ruangtumbuh.test'],
            ['name' => 'Administrator', 'password' => 'password', 'is_admin' => true, 'role' => 'admin']
        );

        // Demo user
        User::firstOrCreate(
            ['email' => 'user@ruangtumbuh.test'],
            ['name' => 'Pengguna Demo', 'password' => 'password', 'is_admin' => false, 'role' => 'user']
        );

        // ===== 1. Assessment Psikologis: Skala Persepsi Stres =====
        // Konstruk: perceived stress (tekanan yang dirasakan dalam sebulan terakhir).
        // 5-point Likert (0-4). Item bertanda reverse=true dibalik skornya karena
        // kalimatnya positif/berlawanan arah dengan konstruk stres (praktik standar
        // psikometri untuk mengurangi bias respons searah / acquiescence bias).
        $this->createLikertAssessment(
            slug: 'cek-kondisi-stres',
            title: 'Cek Kondisi Stres',
            description: 'Refleksi singkat untuk mengenali bagaimana tekanan hadir dalam keseharian Anda selama sebulan terakhir. '
                .'Hasil ini adalah alat refleksi awal, bukan alat diagnosis klinis. Jika Anda merasa kesulitan mengelola stres, '
                .'pertimbangkan untuk berbicara dengan psikolog atau tenaga profesional kesehatan mental.',
            category: 'psychological',
            assessmentType: 'Stres',
            scaleLabels: ['Tidak pernah', 'Hampir tidak pernah', 'Kadang-kadang', 'Sering', 'Sangat sering'],
            interpretationRanges: [
                ['min' => 0, 'max' => 10, 'label' => 'Tingkat stres rendah', 'description' => 'Anda tampak cukup mampu mengelola tekanan sehari-hari.'],
                ['min' => 11, 'max' => 21, 'label' => 'Tingkat stres sedang', 'description' => 'Ada tanda-tanda tekanan yang mulai terasa. Perhatikan pola istirahat dan dukungan sosial Anda.'],
                ['min' => 22, 'max' => 32, 'label' => 'Tingkat stres tinggi', 'description' => 'Tekanan yang Anda rasakan cukup signifikan. Disarankan untuk berbicara dengan profesional kesehatan mental.'],
            ],
            questions: [
                ['text' => 'Saya merasa kewalahan oleh tuntutan yang harus saya selesaikan.', 'reverse' => false],
                ['text' => 'Saya merasa mampu mengendalikan hal-hal penting dalam hidup saya.', 'reverse' => true],
                ['text' => 'Saya merasa gelisah atau tegang tanpa sebab yang jelas.', 'reverse' => false],
                ['text' => 'Saya bisa menemukan cara untuk mengatasi masalah yang muncul.', 'reverse' => true],
                ['text' => 'Saya kesulitan tidur karena terus memikirkan banyak hal.', 'reverse' => false],
                ['text' => 'Saya merasa punya cukup waktu untuk menyelesaikan tugas-tugas saya.', 'reverse' => true],
                ['text' => 'Saya lebih mudah marah atau tersinggung akhir-akhir ini.', 'reverse' => false],
                ['text' => 'Saya merasa didukung oleh orang-orang di sekitar saya saat menghadapi tekanan.', 'reverse' => true],
            ]
        );

        // ===== 2. Assessment Psikologis: Skala Kecemasan =====
        // Konstruk: gejala kecemasan umum (kognitif, fisik, perilaku).
        // 4-point Likert (0-3), semua item forward (kecemasan lazimnya tidak
        // menggunakan reverse item karena gejalanya tidak alami dinyatakan terbalik).
        $this->createLikertAssessment(
            slug: 'cek-tingkat-kecemasan',
            title: 'Cek Tingkat Kecemasan',
            description: 'Refleksi singkat untuk mengenali gejala kecemasan yang mungkin Anda alami dalam dua minggu terakhir. '
                .'Hasil ini adalah alat refleksi awal, bukan alat diagnosis klinis. Kecemasan yang mengganggu aktivitas sehari-hari '
                .'sebaiknya dikonsultasikan dengan psikolog atau psikiater.',
            category: 'psychological',
            assessmentType: 'Kecemasan',
            scaleLabels: ['Tidak pernah', 'Beberapa hari', 'Lebih dari separuh waktu', 'Hampir setiap hari'],
            interpretationRanges: [
                ['min' => 0, 'max' => 7, 'label' => 'Gejala kecemasan minimal', 'description' => 'Gejala kecemasan yang Anda alami tergolong ringan atau jarang muncul.'],
                ['min' => 8, 'max' => 14, 'label' => 'Gejala kecemasan sedang', 'description' => 'Ada gejala kecemasan yang cukup terasa. Perhatikan pemicunya dan pertimbangkan strategi relaksasi.'],
                ['min' => 15, 'max' => 21, 'label' => 'Gejala kecemasan tinggi', 'description' => 'Gejala kecemasan cukup mengganggu. Disarankan berkonsultasi dengan profesional kesehatan mental.'],
            ],
            questions: [
                ['text' => 'Pikiran saya dipenuhi kekhawatiran yang sulit saya hentikan.', 'reverse' => false],
                ['text' => 'Saya merasa jantung berdebar atau napas terasa pendek tanpa aktivitas fisik yang berat.', 'reverse' => false],
                ['text' => 'Saya menghindari situasi tertentu karena takut sesuatu yang buruk akan terjadi.', 'reverse' => false],
                ['text' => 'Saya sulit berkonsentrasi karena pikiran saya dipenuhi kekhawatiran.', 'reverse' => false],
                ['text' => 'Saya merasa tegang secara fisik, seperti otot kaku atau tangan berkeringat.', 'reverse' => false],
                ['text' => 'Saya terbangun di malam hari karena pikiran yang cemas.', 'reverse' => false],
                ['text' => 'Saya merasa was-was meski tidak ada ancaman yang jelas di sekitar saya.', 'reverse' => false],
            ]
        );

        // ===== 3. Assessment Kepribadian: Tipe MBTI =====
        // Forced-choice per dikotomi: Extraversion-Introversion, Sensing-Intuition,
        // Thinking-Feeling, Judging-Perceiving. 3 item per dikotomi (12 total).
        // Skor dihitung per trait_key, trait dengan skor tertinggi di tiap
        // dikotomi menentukan huruf pada kode 4 huruf (mis. INFJ).
        $this->createTraitAssessment(
            slug: 'tipe-kepribadian-mbti',
            title: 'Cari Tipe Kepribadianmu (MBTI)',
            description: 'Kenali kecenderungan kepribadianmu melalui 12 pernyataan sederhana, terinspirasi dari kerangka empat dikotomi kepribadian. '
                .'Hasil ini adalah alat refleksi diri untuk membantu Anda memahami preferensi pribadi, bukan pengukuran psikologis baku atau alat diagnosis.',
            category: 'personality',
            assessmentType: 'MBTI',
            traitMeta: [
                'E' => 'Extraversion', 'I' => 'Introversion',
                'S' => 'Sensing', 'N' => 'Intuition',
                'T' => 'Thinking', 'F' => 'Feeling',
                'J' => 'Judging', 'P' => 'Perceiving',
            ],
            resultMode: 'dichotomy_code', // hasil berupa kode 4 huruf dari trait dominan tiap pasangan
            dichotomyPairs: [['E', 'I'], ['S', 'N'], ['T', 'F'], ['J', 'P']],
            questions: [
                ['text' => 'Saat ada waktu luang, saya lebih suka...', 'options' => [
                    ['label' => 'Menghabiskan waktu bersama banyak orang', 'trait_key' => 'E'],
                    ['label' => 'Menghabiskan waktu sendiri atau dengan segelintir orang dekat', 'trait_key' => 'I'],
                ]],
                ['text' => 'Dalam sebuah acara sosial, saya biasanya...', 'options' => [
                    ['label' => 'Aktif memulai percakapan dengan orang baru', 'trait_key' => 'E'],
                    ['label' => 'Menunggu didekati atau berbicara dengan orang yang sudah saya kenal', 'trait_key' => 'I'],
                ]],
                ['text' => 'Setelah seharian bersosialisasi, saya biasanya merasa...', 'options' => [
                    ['label' => 'Berenergi dan ingin melakukan lebih banyak lagi', 'trait_key' => 'E'],
                    ['label' => 'Lelah dan butuh waktu sendiri untuk memulihkan energi', 'trait_key' => 'I'],
                ]],
                ['text' => 'Saat mempelajari hal baru, saya lebih tertarik pada...', 'options' => [
                    ['label' => 'Fakta konkret dan detail praktis', 'trait_key' => 'S'],
                    ['label' => 'Pola, makna tersembunyi, dan kemungkinan di baliknya', 'trait_key' => 'N'],
                ]],
                ['text' => 'Saya lebih percaya pada...', 'options' => [
                    ['label' => 'Pengalaman langsung dan bukti nyata', 'trait_key' => 'S'],
                    ['label' => 'Intuisi dan gambaran besar', 'trait_key' => 'N'],
                ]],
                ['text' => 'Ketika menjelaskan sesuatu, saya cenderung...', 'options' => [
                    ['label' => 'Menjelaskan langkah demi langkah secara berurutan', 'trait_key' => 'S'],
                    ['label' => 'Melompat ke ide besar terlebih dahulu, baru detail', 'trait_key' => 'N'],
                ]],
                ['text' => 'Saat mengambil keputusan penting, saya lebih mengutamakan...', 'options' => [
                    ['label' => 'Logika dan analisis yang objektif', 'trait_key' => 'T'],
                    ['label' => 'Dampaknya terhadap perasaan orang lain', 'trait_key' => 'F'],
                ]],
                ['text' => 'Saat memberi masukan pada orang lain, saya cenderung...', 'options' => [
                    ['label' => 'Jujur dan langsung ke inti masalah', 'trait_key' => 'T'],
                    ['label' => 'Menyampaikan dengan hati-hati agar tidak menyakiti perasaan', 'trait_key' => 'F'],
                ]],
                ['text' => 'Saya menilai suatu keputusan itu baik jika...', 'options' => [
                    ['label' => 'Masuk akal secara konsisten dan adil bagi semua', 'trait_key' => 'T'],
                    ['label' => 'Terasa selaras dengan nilai dan hubungan yang saya jaga', 'trait_key' => 'F'],
                ]],
                ['text' => 'Dalam mengerjakan proyek, saya lebih nyaman...', 'options' => [
                    ['label' => 'Membuat rencana jelas dan menyelesaikannya tepat waktu', 'trait_key' => 'J'],
                    ['label' => 'Membiarkan rencana fleksibel dan menyesuaikan sambil jalan', 'trait_key' => 'P'],
                ]],
                ['text' => 'Menjelang tenggat waktu, saya biasanya...', 'options' => [
                    ['label' => 'Sudah menyelesaikan sebagian besar pekerjaan jauh-jauh hari', 'trait_key' => 'J'],
                    ['label' => 'Mengerjakan dengan produktif justru mendekati waktu akhir', 'trait_key' => 'P'],
                ]],
                ['text' => 'Saya merasa lebih nyaman ketika...', 'options' => [
                    ['label' => 'Semua sudah terjadwal dan diputuskan sejak awal', 'trait_key' => 'J'],
                    ['label' => 'Masih ada ruang untuk berubah pikiran belakangan', 'trait_key' => 'P'],
                ]],
            ]
        );

        // ===== 4. Assessment Kepribadian: Gaya Perilaku DISC =====
        // Format ipsative (forced-choice): tiap pertanyaan punya 4 opsi yang
        // masing-masing mewakili satu gaya (Dominance, Influence, Steadiness,
        // Compliance). Responden memilih pernyataan yang PALING menggambarkan
        // dirinya. Skor per trait diakumulasi dari jumlah opsi yang dipilih.
        $this->createTraitAssessment(
            slug: 'gaya-perilaku-disc',
            title: 'Kenali Gaya Perilaku (DISC)',
            description: 'Pilih pernyataan yang paling menggambarkan cara Anda biasanya bersikap dalam bekerja dan berinteraksi. '
                .'Hasil ini adalah gambaran kecenderungan gaya perilaku untuk refleksi diri, bukan alat seleksi atau diagnosis psikologis formal.',
            category: 'personality',
            assessmentType: 'DISC',
            traitMeta: [
                'D' => 'Dominance', 'I' => 'Influence', 'S' => 'Steadiness', 'C' => 'Compliance',
            ],
            resultMode: 'dominant_trait', // hasil berupa 1 trait dengan skor tertinggi
            dichotomyPairs: [],
            questions: [
                ['text' => 'Ketika menghadapi masalah di tempat kerja, saya cenderung...', 'options' => [
                    ['label' => 'Langsung mengambil alih dan bertindak cepat', 'trait_key' => 'D'],
                    ['label' => 'Mengajak orang lain berdiskusi dengan antusias', 'trait_key' => 'I'],
                    ['label' => 'Tetap tenang dan mencari solusi bertahap', 'trait_key' => 'S'],
                    ['label' => 'Menganalisis data dan fakta sebelum bertindak', 'trait_key' => 'C'],
                ]],
                ['text' => 'Dalam sebuah tim, peran yang paling nyaman bagi saya adalah...', 'options' => [
                    ['label' => 'Memimpin dan menentukan arah', 'trait_key' => 'D'],
                    ['label' => 'Memotivasi dan menjaga semangat tim', 'trait_key' => 'I'],
                    ['label' => 'Menjaga keharmonisan dan mendukung anggota lain', 'trait_key' => 'S'],
                    ['label' => 'Memastikan detail dan kualitas pekerjaan akurat', 'trait_key' => 'C'],
                ]],
                ['text' => 'Saat menghadapi tekanan waktu, saya biasanya...', 'options' => [
                    ['label' => 'Semakin fokus dan kompetitif untuk segera menuntaskan', 'trait_key' => 'D'],
                    ['label' => 'Mencari dukungan dan bicara dengan orang lain', 'trait_key' => 'I'],
                    ['label' => 'Berusaha tetap stabil dan tidak terburu-buru', 'trait_key' => 'S'],
                    ['label' => 'Membuat daftar prioritas yang terstruktur', 'trait_key' => 'C'],
                ]],
                ['text' => 'Orang lain biasanya menggambarkan saya sebagai orang yang...', 'options' => [
                    ['label' => 'Tegas dan berorientasi hasil', 'trait_key' => 'D'],
                    ['label' => 'Ramah dan mudah bergaul', 'trait_key' => 'I'],
                    ['label' => 'Sabar dan dapat diandalkan', 'trait_key' => 'S'],
                    ['label' => 'Teliti dan sistematis', 'trait_key' => 'C'],
                ]],
                ['text' => 'Ketika ada perubahan mendadak dalam rencana, saya...', 'options' => [
                    ['label' => 'Cepat menyesuaikan dan mencari cara baru untuk tetap menang', 'trait_key' => 'D'],
                    ['label' => 'Mengajak orang lain melihat sisi positif dari perubahan itu', 'trait_key' => 'I'],
                    ['label' => 'Butuh waktu untuk beradaptasi secara bertahap', 'trait_key' => 'S'],
                    ['label' => 'Mengevaluasi ulang rencana secara detail sebelum melanjutkan', 'trait_key' => 'C'],
                ]],
                ['text' => 'Dalam rapat, saya paling sering...', 'options' => [
                    ['label' => 'Mendorong keputusan cepat diambil', 'trait_key' => 'D'],
                    ['label' => 'Melontarkan ide dan membangun semangat diskusi', 'trait_key' => 'I'],
                    ['label' => 'Mendengarkan semua pihak sebelum menanggapi', 'trait_key' => 'S'],
                    ['label' => 'Mengecek apakah semua data sudah akurat', 'trait_key' => 'C'],
                ]],
            ]
        );

        // ===== 5. Assessment Kepribadian: Big Five (OCEAN) =====
        // 10 item, 2 per trait (satu forward, satu reverse) untuk lima trait:
        // Openness, Conscientiousness, Extraversion, Agreeableness, Neuroticism.
        // Skala Likert 1-5. Skor tiap trait dijumlah dari 2 item terkait,
        // dengan salah satu item dibalik nilainya (reverse-scored).
        $this->createBigFiveAssessment(
            slug: 'kepribadian-big-five',
            title: 'Profil Kepribadian Big Five',
            description: 'Refleksikan bagaimana Anda biasanya bersikap dan bereaksi melalui 10 pernyataan singkat, berdasarkan lima dimensi kepribadian utama. '
                .'Hasil ini adalah gambaran umum untuk pengembangan diri, bukan alat seleksi kerja atau diagnosis klinis.',
        );
    }

    /**
     * Assessment berbasis skala Likert dengan skor total dan rentang interpretasi
     * (dipakai untuk Stres & Kecemasan). Mendukung item reverse-scored.
     */
    private function createLikertAssessment(
        string $slug,
        string $title,
        string $description,
        string $category,
        string $assessmentType,
        array $scaleLabels,
        array $interpretationRanges,
        array $questions
    ): void {
        $quiz = Quiz::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => $description,
                'type' => 'assessment',
                'category' => $category,
                'assessment_type' => $assessmentType,
                'duration_minutes' => 5,
                'is_published' => true,
                'interpretation_ranges' => $interpretationRanges,
            ]
        );

        if ($quiz->questions()->exists()) {
            return;
        }

        $scaleCount = count($scaleLabels);

        foreach ($questions as $i => $q) {
            $question = $quiz->questions()->create([
                'question' => $q['text'],
                'type' => 'single_choice',
                'position' => $i + 1,
                'points' => 1,
            ]);

            foreach ($scaleLabels as $j => $label) {
                // Skor forward: index apa adanya (0..n-1).
                // Skor reverse: index dibalik ((n-1)..0), karena kalimat
                // pernyataan berlawanan arah dengan konstruk yang diukur.
                $value = $q['reverse'] ? ($scaleCount - 1 - $j) : $j;

                $question->options()->create([
                    'label' => $label,
                    'value' => $value,
                    'is_correct' => false,
                    'position' => $j + 1,
                ]);
            }
        }
    }

    /**
     * Assessment berbasis trait/kategori (dipakai untuk MBTI & DISC).
     * Tiap opsi punya trait_key sendiri; hasil akhir ditentukan dari
     * akumulasi skor per trait, bukan skor total tunggal.
     */
    private function createTraitAssessment(
        string $slug,
        string $title,
        string $description,
        string $category,
        string $assessmentType,
        array $traitMeta,
        string $resultMode,
        array $dichotomyPairs,
        array $questions
    ): void {
        $quiz = Quiz::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => $description,
                'type' => 'assessment',
                'category' => $category,
                'assessment_type' => $assessmentType,
                'duration_minutes' => 6,
                'is_published' => true,
                // Disimpan sebagai metadata untuk ScoringService, BUKAN rentang skor total,
                // karena hasil assessment ini berbasis trait dominan, bukan skor tunggal.
                'interpretation_ranges' => [
                    'result_mode' => $resultMode,
                    'trait_meta' => $traitMeta,
                    'dichotomy_pairs' => $dichotomyPairs,
                ],
            ]
        );

        if ($quiz->questions()->exists()) {
            return;
        }

        foreach ($questions as $i => $q) {
            $question = $quiz->questions()->create([
                'question' => $q['text'],
                'type' => 'single_choice',
                'position' => $i + 1,
                'points' => 1,
            ]);

            foreach ($q['options'] as $j => $opt) {
                $question->options()->create([
                    'label' => $opt['label'],
                    'value' => 1,
                    'is_correct' => false,
                    'trait_key' => $opt['trait_key'],
                    'position' => $j + 1,
                ]);
            }
        }
    }

    /**
     * Big Five: 5 trait (OCEAN), masing-masing 2 item (1 forward, 1 reverse),
     * skala Likert 1-5.
     */
    private function createBigFiveAssessment(string $slug, string $title, string $description): void
    {
        $quiz = Quiz::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => $description,
                'type' => 'assessment',
                'category' => 'personality',
                'assessment_type' => 'Big Five',
                'duration_minutes' => 6,
                'is_published' => true,
                'interpretation_ranges' => [
                    'result_mode' => 'trait_profile',
                    'trait_meta' => [
                        'O' => 'Openness (Keterbukaan)',
                        'C' => 'Conscientiousness (Kehati-hatian)',
                        'E' => 'Extraversion (Ekstraversi)',
                        'A' => 'Agreeableness (Keramahan)',
                        'N' => 'Neuroticism (Neurotisisme)',
                    ],
                ],
            ]
        );

        if ($quiz->questions()->exists()) {
            return;
        }

        $scaleLabels = ['Sangat tidak setuju', 'Tidak setuju', 'Netral', 'Setuju', 'Sangat setuju'];

        $items = [
            ['text' => 'Saya senang mengeksplorasi ide atau pengalaman baru yang belum pernah saya coba.', 'trait_key' => 'O', 'reverse' => false],
            ['text' => 'Saya lebih nyaman dengan rutinitas yang sudah dikenal daripada mencoba hal baru.', 'trait_key' => 'O', 'reverse' => true],

            ['text' => 'Saya mengerjakan tugas dengan terencana dan memperhatikan detail.', 'trait_key' => 'C', 'reverse' => false],
            ['text' => 'Saya sering menunda pekerjaan sampai mendekati tenggat waktu.', 'trait_key' => 'C', 'reverse' => true],

            ['text' => 'Saya merasa bersemangat ketika berada di tengah banyak orang.', 'trait_key' => 'E', 'reverse' => false],
            ['text' => 'Saya lebih memilih menghabiskan waktu sendirian daripada di keramaian.', 'trait_key' => 'E', 'reverse' => true],

            ['text' => 'Saya mudah percaya dan berusaha memahami sudut pandang orang lain.', 'trait_key' => 'A', 'reverse' => false],
            ['text' => 'Saya cenderung skeptis terhadap niat baik orang lain.', 'trait_key' => 'A', 'reverse' => true],

            ['text' => 'Saya mudah merasa cemas atau khawatir dalam situasi yang menekan.', 'trait_key' => 'N', 'reverse' => false],
            ['text' => 'Saya tetap tenang dan jarang terguncang meski dalam situasi sulit.', 'trait_key' => 'N', 'reverse' => true],
        ];

        foreach ($items as $i => $item) {
            $question = $quiz->questions()->create([
                'question' => $item['text'],
                'type' => 'single_choice',
                'position' => $i + 1,
                'points' => 1,
            ]);

            foreach ($scaleLabels as $j => $label) {
                // Skala 1-5. Reverse: nilai dibalik (5,4,3,2,1) supaya arah
                // skor tetap konsisten terhadap trait yang diukur.
                $value = $item['reverse'] ? (5 - $j) : ($j + 1);

                $question->options()->create([
                    'label' => $label,
                    'value' => $value,
                    'is_correct' => false,
                    'trait_key' => $item['trait_key'],
                    'position' => $j + 1,
                ]);
            }
        }
    }

    // ===== Quiz Pengetahuan (tidak berubah dari versi sebelumnya) =====

    private function createQuizQuiz(string $slug, string $title, string $description, string $category, int $duration, int $passingScore, array $questions): void
    {
        $quiz = Quiz::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => $description,
                'type' => 'quiz',
                'category' => $category,
                'duration_minutes' => $duration,
                'passing_score' => $passingScore,
                'is_published' => true,
            ]
        );

        if (!$quiz->questions()->exists()) {
            foreach ($questions as $i => $q) {
                $question = $quiz->questions()->create([
                    'question' => $q['question'],
                    'type' => 'single_choice',
                    'position' => $i + 1,
                    'points' => $q['points'],
                ]);
                foreach ($q['options'] as $opt) {
                    $question->options()->create([
                        'label' => $opt['label'],
                        'value' => $opt['value'],
                        'is_correct' => $opt['is_correct'],
                        'position' => $opt['position'],
                    ]);
                }
            }
        }
    }
}