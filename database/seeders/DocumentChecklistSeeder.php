<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Checklist dokumen pernikahan berdasarkan agama/adat di Indonesia.
 *
 * CATATAN VERSI: v1.0 (September 2026)
 * Persyaratan dapat berbeda menurut daerah dan berubah seiring waktu.
 * Selalu konfirmasi ke KUA/Catatan Sipil/lembaga terkait di daerahmu.
 * Update seeder ini dan tambah versi baru saat ada perubahan regulasi.
 */
class DocumentChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $version = '1.0';
        $note = 'Persyaratan dapat berbeda menurut daerah dan berubah seiring waktu. Konfirmasi ke instansi terkait setempat.';

        $checklists = [
            // ----------------------------------------------------------------
            // ISLAM — KUA (berlaku nasional, dasar UU No. 1/1974 & KHI)
            // ----------------------------------------------------------------
            [
                'religion' => 'islam',
                'tradition' => null,
                'documents' => [
                    ['title' => 'N1 — Surat Keterangan Untuk Nikah', 'description' => 'Dari RT/RW dan dilegalisir Kelurahan/Desa. Berlaku 6 bulan.'],
                    ['title' => 'N2 — Surat Keterangan Asal-Usul Calon Pengantin', 'description' => 'Dari Kelurahan/Desa masing-masing calon.'],
                    ['title' => 'N4 — Surat Keterangan tentang Orang Tua', 'description' => 'Dari Kelurahan/Desa. Diperlukan jika orang tua sudah meninggal.'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing 2 lembar.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto 2×3 dan 3×4', 'description' => 'Latar biru, masing-masing 4 lembar. Beberapa KUA meminta background merah — konfirmasi dulu.'],
                    ['title' => 'Surat Izin Orang Tua/Wali (N5)', 'description' => 'Wajib jika calon pengantin berusia di bawah 21 tahun.'],
                    ['title' => 'Surat Dispensasi Pengadilan Agama', 'description' => 'Wajib jika salah satu calon belum berusia 19 tahun (sesuai UU No. 16/2019).'],
                    ['title' => 'Surat Keterangan Tidak Halangan Menikah (bagi WNA)', 'description' => 'Dari kedutaan/konsulat negara asal. Khusus jika salah satu WNA.'],
                    ['title' => 'Akta Cerai/Akta Kematian Pasangan Sebelumnya', 'description' => 'Wajib bagi duda/janda. Akta Cerai dari Pengadilan Agama.'],
                    ['title' => 'Surat Izin Poligami dari Pengadilan Agama', 'description' => 'Khusus jika pernikahan poligami (tidak umum, prosedur berbeda).'],
                    ['title' => 'Bukti Imunisasi/Suntik TT (Tetanus Toxoid)', 'description' => 'Calon pengantin wanita. Beberapa daerah masih mewajibkan — cek KUA setempat.'],
                    ['title' => 'Model N7 — Pemberitahuan Kehendak Nikah', 'description' => 'Diisi dan diserahkan ke KUA minimal 10 hari kerja sebelum akad.'],
                ],
            ],

            // ----------------------------------------------------------------
            // KRISTEN PROTESTAN — Gereja
            // ----------------------------------------------------------------
            [
                'religion' => 'kristen',
                'tradition' => null,
                'documents' => [
                    ['title' => 'Surat Baptis', 'description' => 'Dari gereja tempat dibaptis. Beberapa gereja mensyaratkan baptis di gereja yang sama.'],
                    ['title' => 'Surat Sidi / Katekisasi', 'description' => 'Bukti telah mengikuti pengajaran katekisasi. Persyaratan bervariasi antar denominasi.'],
                    ['title' => 'Surat Pengantar dari Gereja Asal', 'description' => 'Jika menikah di gereja berbeda dari gereja asal calon pengantin.'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing 2 lembar.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto Berdampingan', 'description' => 'Ukuran dan jumlah sesuai ketentuan gereja masing-masing.'],
                    ['title' => 'Akta Cerai/Surat Kematian', 'description' => 'Wajib bagi duda/janda. Kebijakan pernikahan kedua berbeda tiap denominasi.'],
                    ['title' => 'Surat Izin Orang Tua', 'description' => 'Jika salah satu atau kedua calon pengantin belum dewasa menurut ketentuan gereja.'],
                    ['title' => 'Pencatatan di Dinas Dukcapil', 'description' => 'Setelah pemberkatan gereja, pasangan mendaftarkan pernikahan ke Catatan Sipil untuk Akta Nikah sipil.'],
                ],
            ],

            // ----------------------------------------------------------------
            // KATOLIK — Paroki / Keuskupan
            // ----------------------------------------------------------------
            [
                'religion' => 'katolik',
                'tradition' => null,
                'documents' => [
                    ['title' => 'Surat Baptis Terbaru (tidak lebih dari 6 bulan)', 'description' => 'Dari paroki tempat dibaptis. Harus baru — diterbitkan ulang jika lebih dari 6 bulan.'],
                    ['title' => 'Surat Krisma', 'description' => 'Bukti telah menerima Sakramen Krisma/Penguatan.'],
                    ['title' => 'Surat Keterangan Bebas Halangan (Nihil Obstat)', 'description' => 'Dari paroki masing-masing calon. Proses sekitar 1–3 bulan sebelum pernikahan.'],
                    ['title' => 'Surat Izin Pernikahan dari Keuskupan', 'description' => 'Diperlukan untuk pernikahan beda agama/beda gereja (dispensasi kanonik).'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto', 'description' => 'Ukuran dan jumlah sesuai ketentuan paroki.'],
                    ['title' => 'Bukti Mengikuti Kursus Persiapan Perkawinan (KPP)', 'description' => 'Sertifikat KPP dari paroki. Biasanya berlangsung beberapa minggu sebelum pernikahan.'],
                    ['title' => 'Akta Perceraian Sipil + Dekrit Anulasi Kanonik', 'description' => 'Untuk duda/janda Katolik. Dekrit anulasi dari tribunal gerejawi bisa memakan waktu berbulan-bulan.'],
                    ['title' => 'Pencatatan di Dinas Dukcapil', 'description' => 'Setelah pemberkatan, daftarkan ke Catatan Sipil untuk Akta Nikah sipil.'],
                ],
            ],

            // ----------------------------------------------------------------
            // HINDU — Parisadha Hindu Dharma Indonesia (PHDI)
            // ----------------------------------------------------------------
            [
                'religion' => 'hindu',
                'tradition' => null,
                'documents' => [
                    ['title' => 'Surat Keterangan Beragama Hindu', 'description' => 'Dari PHDI setempat atau Kelurahan.'],
                    ['title' => 'Surat Pengantar dari PHDI', 'description' => 'Untuk melangsungkan upacara Pawiwahan di Pura/tempat yang ditunjuk PHDI.'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto', 'description' => 'Ukuran dan jumlah sesuai ketentuan PHDI/Pura setempat.'],
                    ['title' => 'Surat Sudhi Wadani', 'description' => 'Wajib jika salah satu calon bukan beragama Hindu sejak lahir (pernyataan masuk Hindu).'],
                    ['title' => 'Pencatatan di Dinas Dukcapil', 'description' => 'Setelah upacara Pawiwahan, daftarkan ke Catatan Sipil untuk Akta Nikah.'],
                ],
            ],

            // ----------------------------------------------------------------
            // BUDDHA — Walubi / Majelis Agama Buddha
            // ----------------------------------------------------------------
            [
                'religion' => 'buddha',
                'tradition' => null,
                'documents' => [
                    ['title' => 'Surat Keterangan Beragama Buddha', 'description' => 'Dari Walubi atau majelis Buddha setempat.'],
                    ['title' => 'Surat Pengantar dari Vihara/Cetiya', 'description' => 'Untuk melangsungkan upacara pemberkatan di vihara yang bersangkutan.'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto', 'description' => 'Ukuran dan jumlah sesuai ketentuan vihara.'],
                    ['title' => 'Pencatatan di Dinas Dukcapil', 'description' => 'Setelah pemberkatan, daftarkan ke Catatan Sipil untuk Akta Nikah.'],
                ],
            ],

            // ----------------------------------------------------------------
            // KONG HU CU — MATAKIN
            // ----------------------------------------------------------------
            [
                'religion' => 'konghucu',
                'tradition' => null,
                'documents' => [
                    ['title' => 'Surat Keterangan Beragama Kong Hu Cu', 'description' => 'Dari MATAKIN (Majelis Tinggi Agama Khonghucu Indonesia) setempat.'],
                    ['title' => 'Surat Pengantar dari Kelenteng/Litang', 'description' => 'Untuk pelaksanaan upacara pernikahan Kong Hu Cu.'],
                    ['title' => 'Fotokopi KTP Calon Pengantin', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Kartu Keluarga (KK)', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Fotokopi Akta Kelahiran', 'description' => 'Masing-masing calon.'],
                    ['title' => 'Pas Foto', 'description' => 'Ukuran dan jumlah sesuai ketentuan.'],
                    ['title' => 'Pencatatan di Dinas Dukcapil', 'description' => 'Setelah upacara, daftarkan ke Catatan Sipil.'],
                ],
            ],
        ];

        foreach ($checklists as $checklist) {
            foreach ($checklist['documents'] as $order => $doc) {
                DB::table('document_checklists')->updateOrInsert(
                    [
                        'religion' => $checklist['religion'],
                        'tradition' => $checklist['tradition'],
                        'title' => $doc['title'],
                    ],
                    [
                        'description' => $doc['description'],
                        'sort_order' => $order + 1,
                        'version' => $version,
                        'version_note' => $note,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command?->info('Document checklist seeded: '.collect($checklists)->sum(fn ($c) => count($c['documents'])).' items.');
    }
}
