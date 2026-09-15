<?php

namespace App\Actions\Weddings;

use App\Models\WeddingProject;
use Carbon\CarbonInterface;

class GenerateWeddingRoadmap
{
    public function handle(WeddingProject $weddingProject): void
    {
        foreach ($this->phases() as $phase) {
            $timelinePhase = $weddingProject->timelinePhases()->create([
                'key' => $phase['key'],
                'name' => $phase['name'],
                'sort_order' => $phase['sort_order'],
                'starts_on' => $this->dateFor($weddingProject, $phase['starts_months_before']),
                'ends_on' => $this->dateFor($weddingProject, $phase['ends_months_before']),
            ]);

            foreach ($phase['tasks'] as $task) {
                $dueDate = null;
                if ($weddingProject->target_date && isset($task['days_before'])) {
                    if ($task['days_before'] >= 0) {
                        $dueDate = $weddingProject->target_date->copy()->subDays($task['days_before']);
                    } else {
                        // Pasca acara
                        $dueDate = $weddingProject->target_date->copy()->addDays(abs($task['days_before']));
                    }
                }

                $timelinePhase->tasks()->create([
                    'title' => $task['title'],
                    'description' => $task['description'] ?? null,
                    'sort_order' => $task['sort_order'],
                    'is_critical' => $task['is_critical'],
                    'due_date' => $dueDate,
                    'status' => 'todo',
                ]);
            }
        }
    }

    private function dateFor(WeddingProject $weddingProject, ?int $monthsBefore): ?CarbonInterface
    {
        if (! $weddingProject->target_date || $monthsBefore === null) {
            return null;
        }

        if ($monthsBefore >= 0) {
            return $weddingProject->target_date->copy()->subMonthsNoOverflow($monthsBefore);
        }

        return $weddingProject->target_date->copy()->addMonthsNoOverflow(abs($monthsBefore));
    }

    /** @return array<int, array<string, mixed>> */
    private function phases(): array
    {
        return [
            [
                'key' => '12_plus_months',
                'name' => '12+ Bulan Sebelum (Pondasi & Konsep)',
                'sort_order' => 1,
                'starts_months_before' => 18,
                'ends_months_before' => 12,
                'tasks' => [
                    ['title' => 'Tentukan visi, konsep pernikahan, dan estimasi budget awal', 'description' => 'Diskusikan bersama pasangan dan kedua keluarga inti.', 'sort_order' => 1, 'is_critical' => true, 'days_before' => 365],
                    ['title' => 'Susun estimasi awal daftar tamu undangan', 'description' => 'Penting untuk kalkulasi kapasitas venue dan porsi catering.', 'sort_order' => 2, 'is_critical' => false, 'days_before' => 350],
                    ['title' => 'Tentukan tanggal target atau alternatif tanggal baik', 'description' => 'Siapkan 2-3 opsi tanggal jika venue pilihan penuh.', 'sort_order' => 3, 'is_critical' => true, 'days_before' => 340],
                ],
            ],
            [
                'key' => '6_to_12_months',
                'name' => '6–12 Bulan Sebelum (Booking Vendor Utama)',
                'sort_order' => 2,
                'starts_months_before' => 12,
                'ends_months_before' => 6,
                'tasks' => [
                    ['title' => 'Survey dan booking Venue / Tempat acara', 'description' => 'Kunci tanggal dan amankan DP venue pilihan.', 'sort_order' => 1, 'is_critical' => true, 'days_before' => 270],
                    ['title' => 'Pilih dan test food Catering', 'description' => 'Pilih paket menu utama, gubukan, dan food testing bersama keluarga.', 'sort_order' => 2, 'is_critical' => true, 'days_before' => 240],
                    ['title' => 'Pilih Wedding Organizer (WO) / Planner', 'description' => 'Tentukan pakai WO D-Day atau Full Planner.', 'sort_order' => 3, 'is_critical' => true, 'days_before' => 210],
                    ['title' => 'Booking Vendor Dokumentasi (Foto & Video)', 'description' => 'Pilih fotografer/videografer sesuai tone visual yang disukai.', 'sort_order' => 4, 'is_critical' => false, 'days_before' => 190],
                    ['title' => 'Booking MUA & Fitting Busana Pengantin', 'description' => 'Amankan slot MUA favorit dan tentukan tema busana adat/modern.', 'sort_order' => 5, 'is_critical' => true, 'days_before' => 180],
                ],
            ],
            [
                'key' => '3_to_6_months',
                'name' => '3–6 Bulan Sebelum (Detail & Dokumen Legal)',
                'sort_order' => 3,
                'starts_months_before' => 6,
                'ends_months_before' => 3,
                'tasks' => [
                    ['title' => 'Booking Dekorasi, Entertainment, dan Sound System', 'description' => 'Cocokkan palet warna dekorasi dengan tema busana.', 'sort_order' => 1, 'is_critical' => false, 'days_before' => 150],
                    ['title' => 'Pesan Cincin Nikah dan Siapkan Mahar / Seserahan', 'description' => 'Perhitungkan waktu pembuatan custom cincin (1-2 bulan).', 'sort_order' => 2, 'is_critical' => true, 'days_before' => 120],
                    ['title' => 'Sesi Foto Prewedding (jika ada)', 'description' => 'Siapkan foto untuk kebutuhan undangan fisik / digital.', 'sort_order' => 3, 'is_critical' => false, 'days_before' => 110],
                    ['title' => 'Mulai urus berkas nikah KUA / Catatan Sipil / Gereja', 'description' => 'Surat pengantar RT/RW, kelurahan (N1-N4), tes kesehatan pranikah.', 'sort_order' => 4, 'is_critical' => true, 'days_before' => 90],
                ],
            ],
            [
                'key' => '1_to_3_months',
                'name' => '1–3 Bulan Sebelum (Undangan & Seragam)',
                'sort_order' => 4,
                'starts_months_before' => 3,
                'ends_months_before' => 1,
                'tasks' => [
                    ['title' => 'Cetak Undangan Fisik & Buat Website Undangan Digital', 'description' => 'Finalisasi daftar nama tamu dan alamat kirim.', 'sort_order' => 1, 'is_critical' => true, 'days_before' => 60],
                    ['title' => 'Pesan Souvenir Pernikahan', 'description' => 'Sesuaikan jumlah souvenir dengan estimasi jumlah tamu.', 'sort_order' => 2, 'is_critical' => false, 'days_before' => 50],
                    ['title' => 'Distribusi kain seragam keluarga dan bridesmaids/groomsmen', 'description' => 'Beri jeda waktu cukup untuk proses jahit seragam.', 'sort_order' => 3, 'is_critical' => false, 'days_before' => 45],
                    ['title' => 'Finalisasi rundown kasar dan susunan panitia keluarga', 'description' => 'Meeting koordinasi awal bersama tim WO dan keluarga inti.', 'sort_order' => 4, 'is_critical' => false, 'days_before' => 30],
                ],
            ],
            [
                'key' => 'h_minus_1_week',
                'name' => 'H-1 Minggu (Final Briefing & Rehearsal)',
                'sort_order' => 5,
                'starts_months_before' => 1,
                'ends_months_before' => 0,
                'tasks' => [
                    ['title' => 'Technical Meeting (TM) seluruh vendor di venue', 'description' => 'Pastikan flow loading barang, sound test, dan rundown fix.', 'sort_order' => 1, 'is_critical' => true, 'days_before' => 7],
                    ['title' => 'Pelunasan sisa tagihan seluruh vendor', 'description' => 'Selesaikan invoice sesuai kontrak masing-masing vendor.', 'sort_order' => 2, 'is_critical' => true, 'days_before' => 5],
                    ['title' => 'Gladi resik / Rehearsal prosesi akad/pemberkatan', 'description' => 'Latihan urutan prosesi, ijab qabul / janji suci.', 'sort_order' => 3, 'is_critical' => false, 'days_before' => 3],
                    ['title' => 'Packing emergency kit dan perlengkapan hari-H', 'description' => 'Cincin, mahar, dokumen asli KUA/Gereja, obat-obatan pribadi.', 'sort_order' => 4, 'is_critical' => true, 'days_before' => 1],
                ],
            ],
            [
                'key' => 'd_day',
                'name' => 'Hari-H (D-Day Acara)',
                'sort_order' => 6,
                'starts_months_before' => 0,
                'ends_months_before' => 0,
                'tasks' => [
                    ['title' => 'Prosesi Akad Nikah / Pemberkatan & Resepsi', 'description' => 'Serahkan teknis ke WO, fokus nikmati momen spesial.', 'sort_order' => 1, 'is_critical' => true, 'days_before' => 0],
                ],
            ],
            [
                'key' => 'post_event',
                'name' => 'Pasca Acara (Serah Terima & Evaluasi)',
                'sort_order' => 7,
                'starts_months_before' => 0,
                'ends_months_before' => -1,
                'tasks' => [
                    ['title' => 'Pengembalian sewa busana / properti adat', 'description' => 'Pastikan busana sewa dikembalikan tepat waktu tanpa denda.', 'sort_order' => 1, 'is_critical' => false, 'days_before' => -2],
                    ['title' => 'Follow up hasil foto & video mentah / teaser', 'description' => 'Cek timeline serah terima album dan video dokumentasi dari vendor.', 'sort_order' => 2, 'is_critical' => false, 'days_before' => -14],
                    ['title' => 'Kirim ucapan terima kasih ke vendor & keluarga', 'description' => 'Apresiasi untuk seluruh pihak yang membantu kelancaran acara.', 'sort_order' => 3, 'is_critical' => false, 'days_before' => -7],
                ],
            ],
        ];
    }
}
