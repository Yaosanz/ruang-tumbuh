# TODO

> **Konteks**: Project ini SUDAH BERJALAN, bukan dimulai dari nol.
> Sebelum menambah fitur baru, **audit dulu kondisi aktual** dengan menjalankan
> command di setiap section "Verifikasi" sebelum mengklaim sesuatu "sudah selesai"
> atau "belum ada". JANGAN berasumsi dari nama file — baca isi filenya.
> JANGAN klaim sudah fix tanpa menjalankan test/reproduksi manual dan menunjukkan
> output aktualnya.
> pastikan code tidak ada yang deprecated sesuai standar penulisan best practices yang sesuai dengan versinya
> pastikan tidak ada kode yang tidak digunakan (unused code) dan tidak ada duplikasi kode (duplicate code) yang tidak perlu
> jangan menambahkan fitur baru sebelum semua checklist di Fase 0-6 selesai dan diverifikasi, karena ini akan mempermudah reviewer untuk menilai kualitas project secara keseluruhan.
> dari codebase yang ada, pastikan semua fitur yang sudah ada berjalan dengan baik, tidak ada bug, dan sesuai dengan requirement yang sudah ditentukan.

**Stack:**: Laravel 11+, Livewire, PHP 8.2+, MySQL/MariaDB, Git.
**Stack aktual project ini** (dari `composer.json` — pakai versi ini sebagai acuan, jangan asumsikan versi lain):

- PHP `^8.4`
- `laravel/framework` `^13.8`
- `livewire/livewire` `^4.3`
- `laravel/tinker` `^3.0`
- Dev tools yang SUDAH TERSEDIA (pakai ini, jangan install ulang/ganti tool lain kalau tidak perlu):
    - `laravel/pint` `^1.27` — code formatter
    - `phpunit/phpunit` `^12.5.12` — test runner (bukan Pest, kecuali dikonfirmasi lain di Fase 4)
    - `mockery/mockery` `^1.6` — mocking untuk test
    - `fakerphp/faker` `^1.23` — data dummy untuk factory/seeder/test
    - `laravel/pail` `^1.2.5` — log viewer real-time (dipakai di `composer dev`)
    - `barryvdh/laravel-ide-helper` `^3.7` — helper autocomplete IDE
    - `doctrine/dbal` `^4.4` — dibutuhkan untuk migration `->change()` (sudah benar ada, jangan install ulang)
    - `nunomaduro/collision` `^8.6` — error reporting CLI yang lebih baik

Semua command di TODO ini disesuaikan dengan script yang SUDAH ADA di `composer.json`
(`composer test`, `composer dev`, `composer setup`) — pakai itu, jangan reinvent command baru.

---



- [ ] Jalankan `git log --oneline -20` — rangkum riwayat perubahan terakhir.
- [ ] Jalankan `git status` — pastikan tidak ada perubahan uncommitted yang tertinggal.
- [ ] Jalankan `php artisan route:list` — catat semua route yang sudah ada, kelompokkan: publik (guest), auth (user), admin.
- [ ] Jalankan `php artisan migrate:status` — pastikan semua migration sudah jalan tanpa pending.
- [ ] Cek `.env` lokal vs kredensial production (Render env vars) — **JANGAN pernah commit `.env` asli**. Pastikan `.env` ada di `.gitignore`.
- [ ] Buka live URL deployment (Render) di browser, screenshot/laporkan kondisi aktual landing page, alur quiz, dan halaman admin.
- [ ] Jalankan `composer test` (ini script resmi di `composer.json`: `config:clear` lalu `php artisan test`, jalan di atas PHPUnit 12.5) — laporkan berapa yang pass/fail dengan output aktual, JANGAN cuma bilang "tests exist".
- [ ] Jalankan `composer dump-autoload` kalau ada perubahan struktur folder/namespace, supaya autoload PSR-4 tetap sinkron.
- [ ] List isi `database/seeders/DatabaseSeeder.php` — konfirmasi 5 quiz yang seharusnya ada: Cek Kondisi Stres, Cek Tingkat Kecemasan, MBTI, DISC, Big Five, plus 1 quiz pengetahuan umum.
- [ ] Buka tiap quiz yang di-seed satu-satu di browser sebagai guest, kerjakan sampai selesai, screenshot halaman hasil masing-masing. Laporkan mana yang OK, mana yang error.

**Setelah Fase 0 selesai, tulis ringkasan singkat status project saat ini sebelum lanjut ke fase berikutnya.**

---

## FASE 1 — Requirement

### 1.1 Quiz Management (Admin)

- [ ] Verifikasi CRUD quiz (create, edit, publish/unpublish, **delete**) berfungsi penuh dari UI admin, bukan cuma dari seeder.
- [ ] Cek validasi: slug unik, judul wajib, kategori wajib, durasi format benar.
- [ ] Pastikan route admin di-guard middleware yang benar (cek `bootstrap/app.php` dan middleware `EnsureAdmin` atau serupa) — coba akses route admin sebagai guest/user biasa, harus ditolak (redirect atau 403), bukan 500 error.
- [ ] Test: buat quiz baru dari admin UI, isi lengkap, publish, cek muncul di landing page publik.

### 1.2 Question Management (Admin)

- [ ] Verifikasi CRUD pertanyaan di dalam quiz (create, edit, delete, reorder).
- [ ] Cek validasi: tipe soal wajib (`single_choice`, `scale`), pertanyaan wajib, opsi jawaban minimal 2.
- [ ] Verifikasi CRUD opsi jawaban, termasuk penentuan `is_correct` untuk quiz pengetahuan dan `trait_key` untuk assessment psikologis.
- [ ] Cek pengurutan (drag-drop atau input `position`) benar-benar tersimpan dan terefleksi di urutan tampil ke user.
- [ ] Pastikan tipe soal `single_choice` dan `scale` didukung penuh dari admin UI (bukan cuma dari seeder).

### 1.3 Quiz Submission (Public)

- [ ] **Kritis**: pastikan bug binding radio button (yang sebelumnya menyebabkan semua jawaban tertimpa nilai terakhir karena `wire:key` hilang) benar-benar sudah fix di SEMUA quiz, bukan cuma yang sempat ditest manual. Cek `⚡take-quiz.blade.php`, pastikan `wire:key` ada di section pertanyaan dan tiap opsi radio.
- [ ] Verifikasi guest bisa submit tanpa login (cek middleware route quiz-taking TIDAK memakai `auth` middleware).
- [ ] Verifikasi validasi: semua pertanyaan wajib terjawab sebelum submit final, tidak bisa submit jawaban dari question_id yang tidak sesuai.
- [ ] Verifikasi rate limiting submission aktif dan berbasis session (bukan IP) — cek implementasi `RateLimiter` di `SubmissionService`/komponen take-quiz.
- [ ] Verifikasi submission tersimpan dalam DB transaction (atomik) — cek `SubmissionService::submit()`.
- [ ] Verifikasi kolom `score`, `max_score`, `percentage` di tabel `submissions` sudah nullable (untuk assessment trait-based yang tidak punya skor tunggal).

### 1.4 Result Display

- [ ] Verifikasi halaman hasil pakai `public_id` (UUID) di URL, bukan raw incrementing ID — cek `Submission::getRouteKeyName()`.
- [ ] Verifikasi tampilan hasil BERBEDA sesuai jenis assessment:
    - Quiz pengetahuan → skor, persentase, status lulus/tidak
    - Stres/Kecemasan → skor + label tingkat + deskripsi
    - MBTI → kode 4 huruf + breakdown skor per dikotomi (E/I, S/N, T/F, J/P)
    - DISC → gaya dominan + skor semua trait
    - Big Five → profil 5 trait (O/C/E/A/N) dengan skor masing-masing
- [ ] Pastikan disclaimer non-klinis muncul di semua hasil assessment psikologis ("bukan alat diagnosis", dsb).
- [ ] Pastikan halaman hasil tidak menampilkan data submission lain (cek query di `SubmissionService::getResult()`).
- [ ] Pastikan halaman hasil tidak bisa diakses ulang setelah 24 jam (cek `created_at` submission vs `now()`).
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi sudah expired (lebih dari 24 jam) — harus 404, bukan expose data submission lain.
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi milik submission user lain — harus 404, bukan expose data submission lain.
- [ ] Test hasil berikan deskripsi trait-based (MBTI/DISC/Big Five) sesuai data seeder, bukan hardcode di Blade view.
- [ ] Test hasil berikan deskripsi yang jelas terkait skor (misal: "Skor tinggi pada trait A menunjukkan ...") — bukan cuma tampilkan angka mentah.
- [ ] Test hasil berikan saran/next step (misal: "untuk mengurangi stres, coba ...") — bukan cuma tampilkan skor mentah.
- [ ] test hasil tampilkan grafik/diagram (misal bar chart untuk DISC, radar chart untuk Big Five) sesuai data submission — bukan hardcode gambar statis.
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi sudah dihapus (soft delete) — harus 404, bukan expose data submission lain.
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi quiz sudah di-unpublish — harus 404, bukan expose data submission lain.
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi quiz sudah dihapus (soft delete) — harus 404, bukan expose data submission lain.
- [ ] Test akses halaman hasil dengan `public_id` yang valid tapi quiz sudah expired (misal ada field `expires_at`) — harus 404, bukan expose data submission lain.
- [ ] Test akses halaman hasil dengan `public_id` acak/tidak valid — harus 404, bukan expose data submission lain.

### 1.5 Responsive UI

- [ ] Test di viewport mobile (375px), tablet (768px), desktop — cek landing page, halaman quiz-taking, halaman hasil, dan SEMUA halaman admin (bukan cuma publik).
- [ ] Pastikan tidak ada horizontal overflow di mobile.
- [ ] Cek loading state (`wire:loading`) ada di semua tombol yang trigger request (start, next, submit).
- [ ] Pastikan semua tombol submit/next/finish disabled saat loading, untuk mencegah double submit.
- [ ] Cek error handling: kalau ada error server (500) saat submit, tampilkan alert friendly, jangan crash halaman.
- [ ] Cek error handling: kalau ada error validasi (422), tampilkan pesan field-specific, jangan generic.
- [ ] Pastikan semua alert/error message bisa di-dismiss (close button) dan tidak menumpuk di UI.
- [ ] Cek semua form input (text, radio, checkbox) punya label yang jelas untuk aksesibilitas (a11y).
- [ ] Pastikan semua tombol dan link punya `aria-label` atau teks yang jelas untuk screen reader.
- [ ] Cek kontras warna teks vs background sesuai WCAG AA (minimal 4.5:1 untuk teks normal).
- [ ] Admin UI: cek semua tabel data (quiz list, question list, submission list) punya pagination, search, dan sort yang berfungsi.
- [ ] Admin UI: cek semua form (quiz, question, option) punya validasi client-side (Livewire) dan server-side (Laravel), tampilkan error message di UI.

---

## FASE 2 — RBAC & Auth

- [ ] Integrasikan JWT Auth (Laravel Sanctum) untuk API route `/api/quizzes` dan `/api/submissions` — cek token-based auth bekerja untuk user/admin, dan guest tidak bisa akses.
- [ ] Verifikasi 3 level akses bekerja benar:
    - **Guest**: bisa lihat landing page + katalog + mengerjakan quiz TANPA login.
    - **User (login)**: semua hak guest + riwayat submission + dashboard pribadi
    - **Admin**: akses penuh CMS, TIDAK bisa diakses oleh guest/user biasa.
- [ ] Verifikasi 3 level akses bekerja benar:
    - **Guest**: bisa lihat landing page + katalog + mengerjakan quiz TANPA login.
    - **User (login)**: semua hak guest + riwayat submission + dashboard pribadi.
    - **Admin**: akses penuh CMS, TIDAK bisa diakses oleh guest/user biasa.
- [ ] Verifikasi login admin dan login user memakai flow yang SAMA (`Auth::attempt()` tanpa filter role), dengan redirect berbeda berdasarkan role setelah login sukses — cek ini benar-benar diterapkan (dulu sempat ada bug filter role di `Auth::attempt()` yang menyebabkan admin gagal login).
- [ ] Verifikasi middleware `admin` (bukan filter di proses login) yang menjaga route `/admin/*`.
- [ ] Test: register user baru, login, cek redirect ke `/dashboard` (bukan admin dashboard).
- [ ] Test: login sebagai admin dari `/login` biasa, cek redirect ke admin dashboard.
- [ ] Verifikasi dashboard user menampilkan riwayat submission miliknya SENDIRI saja (bukan submission user lain).
- [ ] Cek password di seeder tidak double-hashed (verifikasi cast `'password' => 'hashed'` di model `User` tidak dipakai bersamaan dengan `Hash::make()` manual di seeder — pilih salah satu).

---

## FASE 3 — Service Layer & Repository Pattern

- [ ] Baca `app/Services/ScoringService.php` — verifikasi menangani 4 mode dengan benar: score-based (default), `dichotomy_code` (MBTI), `dominant_trait` (DISC), `trait_profile` (Big Five). Test tiap mode dengan submission asli, bukan cuma baca kode.
- [ ] Baca `app/Services/SubmissionService.php` dan `app/Services/InterpretationService.php` — pastikan tidak ada logic bisnis yang bocor ke Livewire component atau Blade view.
- [ ] Cek apakah ada Repository layer terpisah (`app/Repositories/`) untuk akses data Quiz dan Submission, atau apakah Eloquent model dipanggil langsung dari Service — kalau yang terakhir, evaluasi apakah perlu direfactor untuk nilai tambah "Repository Pattern" di .
- [ ] Pastikan tidak ada duplikasi logic interpretasi antara `ScoringService` dan `InterpretationService` (sempat ada bug dulu: `InterpretationService` re-derive range secara terpisah dan bisa error kalau struktur data trait-based).

---

## FASE 4 — Testing

> Project ini pakai PHPUnit 12.5 (bukan Pest, meski `pestphp/pest-plugin` ada di
> `allow-plugins` — cek dulu apakah Pest benar-benar dipakai atau itu cuma sisa
> default skeleton Laravel). Test class ikuti konvensi PHPUnit standar
> (`extends TestCase`, method `public function test_xxx()` atau atribut `#[Test]`),
> KECUALI kamu konfirmasi project ini memang pakai sintaks Pest (`it('...', fn () => ...)`).
> Cek isi folder `tests/Feature` dan `tests/Unit` dulu untuk tahu konvensi mana yang dipakai.

- [ ] Cek `tests/Feature` dan `tests/Unit` — tentukan konvensi yang sudah dipakai (PHPUnit class-based vs Pest function-based), ikuti yang SAMA, jangan campur dua gaya dalam satu project.
- [ ] Tulis/verifikasi feature test untuk: submission flow end-to-end (guest mengerjakan quiz sampai lihat hasil) — untuk MINIMAL 2 tipe: 1 quiz pengetahuan + 1 assessment trait-based (MBTI/DISC/Big Five).
- [ ] Tulis/verifikasi unit test untuk `ScoringService` — test keempat mode (score-based, dichotomy, dominant_trait, trait_profile) dengan input terkontrol, assert output sesuai ekspektasi. Gunakan `Mockery` (sudah ada di `require-dev`) untuk mock dependency kalau perlu isolasi.
- [ ] Tulis feature test: guest tidak bisa akses route admin.
- [ ] Tulis feature test: user tidak bisa akses route admin.
- [ ] Tulis feature test: admin bisa CRUD quiz.
- [ ] Gunakan `fakerphp/faker` (sudah ada di `require-dev`) untuk generate data dummy di factory/test, jangan hardcode data test manual kalau bisa pakai faker.
- [ ] Jalankan `composer test` di akhir (bukan `php artisan test` langsung, supaya config cache ikut ke-clear dulu sesuai script resmi project), laporkan hasil aktual (jumlah pass/fail), bukan asumsi.

---

## FASE 5 — Line Ending & Code Hygiene

- [ ] Pastikan `.gitattributes` sudah ter-commit (mencegah CRLF drift yang sempat menyebabkan Intelephense salah baca nomor baris).
- [ ] Jalankan `grep -rlU $'\r' app/ resources/ --include="*.php" --include="*.blade.php"` — pastikan tidak ada file lain yang masih CRLF.
- [ ] Jalankan `vendor/bin/pint` (Laravel Pint `^1.27`, sudah ada di `composer.json` require-dev) untuk format code otomatis sesuai standar Laravel. Kalau mau lihat dulu apa yang akan diubah tanpa langsung apply: `vendor/bin/pint --test`.
- [ ] Setelah Pint jalan, commit terpisah khusus formatting (`chore: format code with Pint`) — jangan gabung dengan commit fitur/bugfix supaya diff-nya bersih dan gampang di-review.
- [ ] Kalau ingin bantuan IDE lebih baik (autocomplete Eloquent model, facade, dsb), jalankan `php artisan ide-helper:generate`, `php artisan ide-helper:models`, `php artisan ide-helper:meta` (dari `barryvdh/laravel-ide-helper` yang sudah ada di require-dev) — opsional, tidak wajib untuk submission, tapi membantu development lokal.

---

## FASE 6 — Dokumentasi (, murah untuk dikerjakan, dampak besar ke kesan profesional)

Buat/perbarui `README.md` berisi:

- [ ] Deskripsi singkat aplikasi (Quiz & Assessment Management System).
- [ ] Tech stack yang dipakai: Laravel `^13.8`, Livewire `^4.3`, PHP `^8.4`, MySQL (via Aiven), Docker, Render. Sebutkan versi persis dari `composer.json`, bukan versi generik.
- [ ] Cara setup lokal, SESUAI script `composer.json` yang sudah ada (jangan tulis ulang manual kalau sudah ada script resmi):
    ```bash
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    npm install
    npm run build
    php artisan serve
    ```
    Atau sebutkan bahwa `composer setup` sudah otomatis menjalankan sebagian besar langkah ini (cek isi script `"setup"` di `composer.json` — tapi catat bahwa script itu belum include `npm run build` untuk hasil akhir, cuma `npm install`, jadi tetap perlu jalanin build manual atau pakai mode dev).
- [ ] Cara development sehari-hari: `composer dev` (menjalankan `php artisan serve` + `queue:listen` + `pail` (log viewer real-time) + `npm run dev` secara bersamaan lewat `concurrently`) — sebutkan ini di README supaya reviewer yang mau coba jalanin lokal tidak bingung harus buka banyak terminal manual.
- [ ] Kredensial demo: admin (`admin@ruangtumbuh.test` / `password`) dan user (`user@ruangtumbuh.test` / `password`) — **pastikan password seeder ini memang masih `password` di versi final, atau update dokumentasi kalau sudah diganti**.
- [ ] Penjelasan singkat arsitektur (Service Layer, Repository, alur scoring 4-mode).
- [ ] Cara menjalankan test: `composer test` (bukan `php artisan test` langsung — script resmi project clear config cache dulu).
- [ ] Cara menjalankan via Docker (`docker build`, atau catatan bahwa deployment dilakukan via Render).
- [ ] Link Live URL deployment.
- [ ] Daftar yang diimplementasikan (checklist), supaya reviewer tidak perlu menebak.

---

## Aturan untuk Agent Selama Mengerjakan Ini

- Setelah menyelesaikan setiap checklist item, **tunjukkan bukti konkret** (output command, screenshot, atau potongan log) — bukan cuma "sudah selesai".
- Kalau menemukan bug, **reproduksi manual dulu** sebelum klaim root cause, dan **verifikasi ulang setelah fix** — jangan asumsikan fix pertama sudah benar tanpa testing ulang end-to-end.
- Kalau ragu antara dua pendekatan, tanyakan dulu sebelum implementasi besar-besaran.
- Jangan hapus/timpa data seeder yang sudah bekerja tanpa konfirmasi.
