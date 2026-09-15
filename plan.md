# Implementation Plan — Nol ke Nikah

## Tujuan MVP

Membantu calon pengantin Indonesia menyusun persiapan pernikahan dari nol melalui onboarding, roadmap dinamis, task management, vendor management, budget tracking, dan dashboard ringkasan.

## Fondasi yang Digunakan

Proyek sudah menggunakan Laravel 13, Inertia 3 Vue, Fortify, Wayfinder, Tailwind CSS v4, serta sistem `Team` dan invitation bawaan.

| Kebutuhan produk | Implementasi |
| --- | --- |
| Satu proyek pernikahan dan kolaborasi | `Team` menjadi workspace proyek pernikahan |
| Detail pernikahan | Tabel `wedding_projects`, relasi satu-ke-satu dengan `teams` |
| Owner, pasangan, keluarga, dan WO | Tetap memakai `team_members`; fase kolaborasi menambah `access_level` (`full` / `read_only`) dan label hubungan |
| Budget aktual | Dihitung dari agregasi `payments`, bukan nilai terpakai yang disimpan dan berisiko tidak sinkron |
| Notifikasi in-app | Laravel database notifications bawaan dengan `wedding_project_id` dalam payload |
| Nominal uang | Integer Rupiah (`amount_idr`), bukan float |
| Task terlewat | Keadaan visual dari `due_date` dan task yang belum selesai, bukan status yang disimpan |

Sistem ini menghindari dua mekanisme membership dan invitation. Registrasi saat ini sudah membuat personal team; onboarding akan mengubahnya menjadi workspace, misalnya “Pernikahan Rendra & Pasangan”.

## Fase 1 — Domain dan Onboarding ✅

1. ✅ Tambahkan enum PHP untuk status proyek, task, vendor, dokumen, dan jenis pembayaran.
2. ✅ Buat migrasi, model, factory, serta seeder untuk:
   - `wedding_projects`
   - `timeline_phases`
   - `tasks`
   - `vendor_categories`
   - `vendors`
   - `budget_allocations`
   - `payments`
3. ✅ Seed kategori vendor Indonesia: venue, catering, WO, dekorasi, dokumentasi, MUA/attire, undangan, souvenir, entertainment, transportasi, serta cincin/mahar.
4. ✅ Buat onboarding wizard empat langkah:
   - nama pasangan dan proyek;
   - tanggal target atau pilihan “belum tahu tanggal”;
   - budget dan estimasi tamu;
   - kota, agama, dan adat.
5. ✅ Simpan onboarding dan generate roadmap dalam satu transaksi melalui action `GenerateWeddingRoadmap`.
6. ✅ Ubah redirect setelah registrasi menuju onboarding, bukan dashboard kosong.

Jika tanggal target belum ada, fase dan checklist tetap dibuat tanpa deadline absolut. Ketika tanggal dimasukkan kemudian, sistem menghitung ulang deadline task template tanpa menghapus task custom, task yang diedit, atau task selesai.

## Fase 2 — Roadmap dan Task Management ✅

1. ✅ Sediakan template roadmap Indonesia berdasarkan hitungan mundur:
   - 12+ bulan;
   - 6–12 bulan;
   - 3–6 bulan;
   - 1–3 bulan;
   - H-1 minggu;
   - hari-H;
   - pasca acara.
2. ✅ Setiap task template memiliki urutan, prioritas, deskripsi, offset deadline, dan penanda kritis.
3. ✅ Implementasikan halaman timeline untuk:
   - melihat fase aktif dan progress;
   - melihat task mendesak dan overdue;
   - mengubah status Belum Mulai / Sedang Proses / Selesai;
   - menambah, mengedit, menghapus, dan meng-assign task pribadi.
4. ✅ Tampilkan task melewati deadline dengan warna merah secara otomatis; tidak diperlukan scheduler untuk memperbarui status ini.
5. ✅ Batasi seluruh query dengan workspace aktif dan kembalikan 404 untuk sumber daya lintas proyek.

## Fase 3 — Vendor dan Budget MVP ✅

1. ✅ Buat halaman vendor per kategori untuk mengelola nama, kontak, quote, catatan, tanggal follow-up, serta status Riset / Nego / Booked / DP / Lunas / Dibatalkan.
2. ✅ Buat halaman budget dengan alokasi per kategori yang bisa disesuaikan dari persentase rekomendasi.
3. ✅ Catat pembayaran vendor untuk DP, cicilan, dan pelunasan; dukung juga pengeluaran manual tanpa vendor.
4. ✅ Tampilkan per kategori: rencana, aktual, selisih, persentase penggunaan, dan warning over-budget.
5. ✅ Gunakan eager loading, `withCount`, serta agregasi database untuk mencegah N+1 query.

## Fase 4 — Dashboard dan UI ✅

1. ✅ Ganti dashboard placeholder dengan:
   - progress persiapan keseluruhan;
   - tiga sampai lima task paling mendesak;
   - jumlah task terlewat;
   - budget terpakai vs total;
   - vendor yang perlu follow-up.
2. ✅ Perluas sidebar menjadi Dashboard, Timeline, Vendor, Budget, Dokumen, dan Pengaturan.
3. ✅ Gunakan `<Form>` atau `useForm` dari Inertia untuk form, dan Wayfinder untuk seluruh route/action frontend tanpa URL hardcoded.
4. ✅ Gunakan komponen UI yang sudah tersedia, desain mobile-first, dukungan dark mode, serta Tailwind CSS v4.
5. ✅ Pertahankan route berbasis workspace:
   - `/{current_team}/onboarding`
   - `/{current_team}/dashboard`
   - `/{current_team}/timeline`
   - `/{current_team}/vendors`
   - `/{current_team}/budget`
   - `/{current_team}/documents`

## Fase 5 — Keamanan dan Kualitas ✅

1. ✅ Buat policy untuk proyek, task, vendor, pembayaran, dan dokumen.
2. ✅ Gunakan Form Request untuk semua perubahan data dan batasi atribut model yang dapat diisi secara massal.
3. ✅ Simpan kontrak dan invoice pada private disk, dengan validasi MIME type, ukuran file, dan endpoint download berotorisasi.
4. ✅ Render catatan vendor dan input teks sebagai teks aman tanpa HTML mentah.
5. ✅ Tambahkan feature test Pest untuk:
   - onboarding valid/invalid serta generation roadmap;
   - isolasi antar-workspace;
   - akses read-only;
   - CRUD task dan perhitungan overdue;
   - CRUD vendor dan pembayaran;
   - total budget serta warning over-budget;
   - upload dokumen dengan `Storage::fake()`;
   - prop Inertia dashboard.
6. ✅ Gunakan waktu palsu untuk test deadline dan reminder, serta fake untuk notification, queue, dan storage.
7. ⚠️ Setelah perubahan route, regenerasi Wayfinder; jalankan test terkait, Pint, dan type check Vue. (Wayfinder di-generate, Pint belum dijalankan.)

## Fase 6 — Kolaborasi dan Reminder ✅

Setelah MVP stabil:

1. ✅ Rebrand halaman Team menjadi pengaturan proyek dan anggota.
2. ✅ Tambahkan pasangan, keluarga, dan WO melalui invitation yang sudah tersedia.
3. ✅ Tambahkan `access_level` untuk membedakan read-only dan full access.
4. ⚠️ Tambahkan activity log untuk perubahan vendor, budget, dan task. (Belum diimplementasikan — post-MVP.)
5. ✅ Tambahkan checklist dokumen berdasarkan agama/adat. Konten harus diberi versi dan catatan bahwa persyaratan dapat berbeda menurut daerah maupun waktu.
6. ✅ Buat command reminder harian yang mendeteksi deadline dekat dan overdue, menyimpan log pengiriman untuk mencegah duplikasi, serta mengirim notifikasi database/email lewat queue.
7. ✅ Jadwalkan reminder dengan `withoutOverlapping()` dan `onOneServer()` untuk production. Mulai dengan database queue yang tersedia; Redis/Horizon dapat dipilih ketika kebutuhan volume dan monitoring meningkat.

## Batas Selesai MVP ✅

MVP dianggap selesai ketika pengguna dapat mendaftar, menuntaskan onboarding, menerima roadmap sesuai tanggal target, mengelola task, vendor, alokasi serta pengeluaran budget, dan melihat ringkasan dashboard; seluruh data harus terisolasi per workspace.

**Status: 128 tests passed (472 assertions), Vite build berhasil.**
