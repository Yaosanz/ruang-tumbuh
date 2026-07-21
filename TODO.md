# TODO - Perbaikan Keseluruhan

## Tahap 1: Migrations (Clean Slate)

- [x] Perbarui `094905_create_quizzes_table.php` — tambah semua kolom langsung
- [x] Perbarui `094908_create_submissions_table.php` — tambah public_id, user_id dll langsung
- [x] Hapus migrasi redundant: 094910, 120000, 120100, 120200

## Tahap 2: Fix Take Quiz Component

- [x] Fix validasi next() — reset error + pesan spesifik
- [x] Fix navigasi — tidak reset state saat error
- [x] Fix submit — handle error dengan baik

## Tahap 3: Fix SubmissionService

- [x] Fix error handling resolveOptions
- [x] Validasi jawaban dari komponen, bukan service

## Tahap 4: Seeder

- [x] Buat 4 quiz berbeda (semua kategori)
- [x] User admin + user demo
- [x] Skenario berbeda: quiz + assessment, Likert + pilihan ganda

## Tahap 5: Landing Page

- [x] Hapus link Admin di navbar landing
- [x] Hapus link CMS Admin di footer

## Tahap 6: Dashboard User

- [x] Tampilkan link CMS hanya jika role admin

## Tahap 7: Test & Deploy

- [x] Reset database
- [x] Test seluruh fitur
