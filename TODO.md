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

**Stack aktual project ini** (dari `composer.json`):

- PHP `^8.4`
- `laravel/framework` `^13.8`
- `livewire/livewire` `^4.3`
- `laravel/tinker` `^3.0`
- Dev tools yang SUDAH TERSEDIA:
    - `laravel/pint` `^1.27` — code formatter
    - `phpunit/phpunit` `^12.5.12` — test runner
    - `mockery/mockery` `^1.6` — mocking
    - `fakerphp/faker` `^1.23` — data dummy
    - `laravel/pail` `^1.2.5` — log viewer
    - `barryvdh/laravel-ide-helper` `^3.7` — IDE autocomplete
    - `doctrine/dbal` `^4.4` — migration `->change()`
    - `nunomaduro/collision` `^8.6` — error reporting CLI

Semua command di TODO ini pakai script yang SUDAH ADA di `composer.json` (`composer test`, `composer dev`, `composer setup`).

---

## FASE 0 — Audit Kondisi Aktual ✅

> **Status: SELESAI** — Semua checklist telah diverifikasi via terminal.

### Checklist Progress

- [x] `git log --oneline -20` — HEAD di `main`, up-to-date dengan remotes
- [x] `git status` — **10 file termodifikasi belum di-commit**
- [x] `php artisan route:list` — 25 routes (publik, auth, admin, API, Livewire)
- [x] `php artisan migrate:status` — 9/9 migration sudah di [1] Ran
- [x] Cek `.gitignore` — `.env`, `.env.*`, `_ide_helper.php`, `.phpstorm.meta.php` sudah di-ignore
- [ ] Buka live URL deployment (Render) — **perlu browser manual**
- [x] `composer test` — ✅ **39 tests passed (102 assertions)**, 0 failures
- [x] `composer dump-autoload` — ✅ Generated optimized autoload files
- [x] Cek `database/seeders/DatabaseSeeder.php` — ✅ 5 assessment di-seed
- [ ] Buka tiap quiz di browser — **perlu akses manual**

### Ringkasan Status

- **Migration**: 9/9 up-to-date ✅
- **Tests**: 39 passing (102 assertions) ✅
- **Seeded content**: 5 assessment psikologis (Stres, Kecemasan, MBTI, DISC, Big Five)
- **Routes**: Landing page, quiz-taking, result, auth, admin, API — semua terdaftar
- **Uncommitted**: 10 file termodifikasi (model, controller, middleware, service, config)
- **Catatan**: Tidak ada quiz pengetahuan (`type='quiz'`) di seeder — hanya assessment

---

## FASE 1 — Requirement

### 1.1 Quiz Management (Admin)

- [ ] Verifikasi CRUD quiz (create, edit, publish/unpublish, delete) berfungsi penuh dari UI admin
- [ ] Cek validasi: slug unik, judul wajib, kategori wajib, durasi format benar
- [ ] Pastikan route admin di-guard middleware yang benar — akses guest/user harus ditolak (redirect/403)
- [ ] Test: buat quiz baru dari admin UI, isi lengkap, publish, cek muncul di landing page publik

### 1.2 Question Management (Admin)

- [ ] Verifikasi CRUD pertanyaan (create, edit, delete, reorder)
- [ ] Cek validasi: tipe soal wajib (`single_choice`, `scale`), pertanyaan wajib, opsi minimal 2
- [ ] Verifikasi CRUD opsi jawaban termasuk `is_correct` dan `trait_key`
- [ ] Cek pengurutan (position) benar-benar tersimpan dan terefleksi di tampilan user
- [ ] Pastikan tipe soal `single_choice` dan `scale` didukung penuh dari admin UI

### 1.3 Quiz Submission (Public)

- [ ] **Kritis**: pastikan bug binding radio button fix di SEMUA quiz — cek `wire:key` di `⚡take-quiz.blade.php`
- [ ] Verifikasi guest bisa submit tanpa login
- [ ] Verifikasi validasi: semua pertanyaan wajib terjawab, tidak bisa submit jawaban dari question_id lain
- [ ] Verifikasi rate limiting submission aktif dan berbasis session
- [ ] Verifikasi submission tersimpan dalam DB transaction (atomik)
- [ ] Verifikasi kolom `score`, `max_score`, `percentage` nullable (untuk trait-based assessment)

### 1.4 Result Display

- [ ] Verifikasi halaman hasil pakai `public_id` (UUID), bukan raw incrementing ID
- [ ] Verifikasi tampilan hasil BERBEDA sesuai jenis assessment (Stres, Kecemasan, MBTI, DISC, Big Five)
- [ ] Pastikan disclaimer non-klinis muncul di semua hasil assessment psikologis
- [ ] Pastikan halaman hasil tidak menampilkan data submission lain
- [ ] Pastikan halaman hasil tidak bisa diakses ulang setelah 24 jam
- [ ] Test berbagai skenario akses hasil (expired, user lain, quiz dihapus, random ID) — harus 404

### 1.5 Responsive UI

- [ ] Test di viewport mobile (375px), tablet (768px), desktop
- [ ] Pastikan tidak ada horizontal overflow di mobile
- [ ] Cek loading state (`wire:loading`) di semua tombol trigger request
- [ ] Pastikan tombol submit/next/finish disabled saat loading
- [ ] Cek error handling server (500) dan validasi (422) — tampilkan alert friendly
- [ ] Pastikan semua form input punya label jelas untuk aksesibilitas (a11y)
- [ ] Admin UI: cek tabel data punya pagination, search, sort

---

## FASE 2 — RBAC & Auth

- [ ] Integrasikan JWT Auth (Laravel Sanctum) untuk API route
- [ ] Verifikasi 3 level akses: Guest, User, Admin
- [ ] Verifikasi login admin/user pakai flow sama (`Auth::attempt()`), redirect berbeda berdasarkan role
- [ ] Verifikasi middleware `admin` menjaga route `/admin/*`
- [ ] Cek password di seeder tidak double-hashed (cast `'hashed'` vs `Hash::make()` manual)

---

## FASE 3 — Service Layer & Repository Pattern

- [ ] Baca `ScoringService.php` — verifikasi 4 mode scoring (score-based, dichotomy, dominant_trait, trait_profile)
- [ ] Baca `SubmissionService.php` & `InterpretationService.php` — pastikan tidak ada logic bocor ke Livewire/Blade
- [ ] Cek Repository layer terpisah — evaluasi apakah perlu refactor
- [ ] Pastikan tidak ada duplikasi logic interpretasi antara `ScoringService` dan `InterpretationService`

---

## FASE 4 — Testing

- [ ] Cek konvensi test di `tests/Feature` dan `tests/Unit` (PHPUnit class-based vs Pest)
- [ ] Tulis feature test: submission flow end-to-end (min 2 tipe: quiz pengetahuan + trait-based)
- [ ] Tulis unit test: `ScoringService` 4 mode dengan Mockery
- [ ] Tulis feature test: guest/user tidak bisa akses admin
- [ ] Tulis feature test: admin CRUD quiz
- [ ] Jalankan `composer test` di akhir

---

## FASE 5 — Code Hygiene

- [ ] Pastikan `.gitattributes` sudah ter-commit
- [ ] Cek tidak ada file CRLF di `app/` dan `resources/`
- [ ] Jalankan `vendor/bin/pint --test` dulu, lalu `vendor/bin/pint` untuk format otomatis
- [ ] Commit terpisah khusus formatting (`chore: format code with Pint`)
- [ ] Opsional: `php artisan ide-helper:generate`, `ide-helper:models`, `ide-helper:meta`

---

## FASE 6 — Dokumentasi

- [ ] Update `README.md` dengan deskripsi, tech stack, cara setup, kredensial demo, arsitektur, testing, deployment
- [ ] Link Live URL deployment

---

## Aturan

- Setiap checklist item selesai → **tunjukkan bukti konkret** (output command, screenshot, log)
- Kalau menemukan bug → **reproduksi manual dulu** sebelum klaim root cause, **verifikasi ulang** setelah fix
- Kalau ragu antara dua pendekatan → tanya dulu sebelum implementasi besar
- Jangan hapus/timpa data seeder yang sudah bekerja tanpa konfirmasi
