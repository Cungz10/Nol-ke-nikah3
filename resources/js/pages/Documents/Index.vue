<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2, Download, FileText, FileSpreadsheet, FileCode, Paperclip } from '@lucide/vue';
import type { BreadcrumbItem } from '@/types';

interface Vendor {
    id: number;
    name: string;
}

interface WeddingDocument {
    id: number;
    wedding_project_id: number;
    vendor_id?: number;
    title: string;
    category: string;
    file_path: string;
    file_name: string;
    mime_type?: string;
    file_size_bytes: number;
    notes?: string;
    created_at: string;
    vendor?: Vendor;
}

const props = defineProps<{
    weddingProject: { id: number; name: string; religion?: string; tradition?: string };
    documents: WeddingDocument[];
    vendors: Vendor[];
    checklist?: Array<{
        id: number;
        title: string;
        description?: string;
        sort_order: number;
        version: string;
        version_note?: string;
        religion?: string | null;
        tradition?: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
    { title: 'Dokumen & Lampiran', href: '#' },
];

const selectedCategory = ref<string>('all');
const isUploadModalOpen = ref(false);

const filteredDocs = computed(() => {
    if (selectedCategory.value === 'all') return props.documents;
    return props.documents.filter(d => d.category === selectedCategory.value);
});

const formatBytes = (bytes: number, decimals = 1) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};

const docForm = useForm({
    title: '',
    category: 'general',
    vendor_id: null as number | null,
    file: null as File | null,
    notes: '',
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        docForm.file = target.files[0];
        if (!docForm.title) {
            docForm.title = target.files[0].name.replace(/\.[^/.]+$/, '');
        }
    }
};

const submitDocument = () => {
    docForm.post(window.location.pathname, {
        forceFormData: true,
        onSuccess: () => {
            isUploadModalOpen.value = false;
            docForm.reset();
        },
    });
};

const deleteDocument = (doc: WeddingDocument) => {
    if (confirm(`Hapus dokumen "${doc.title}"?`)) {
        useForm({}).delete(window.location.pathname + '/' + doc.id);
    }
};

const getCategoryLabel = (cat: string) => {
    const map: Record<string, string> = {
        general: 'Umum / Lainnya',
        legal_kua: 'Berkas Legal KUA',
        legal_church: 'Berkas Legal Catatan Sipil/Gereja',
        vendor_contract: 'Kontrak / MOU Vendor',
        invoice: 'Kwitansi / Invoice',
        rundown: 'Rundown & Panduan Acara',
        other: 'Lain-lain',
    };
    return map[cat] || cat;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dokumen Pernikahan" />

        <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Dokumen & Lampiran</h1>
                    <p class="text-sm text-neutral-500">Penyimpanan aman (private storage) untuk berkas, kontrak, dan invoice {{ weddingProject.name }}</p>
                </div>
                <button
                    @click="isUploadModalOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 rounded-lg text-sm font-medium hover:opacity-90 transition"
                >
                    <Plus class="w-4 h-4" />
                    Unggah Dokumen
                </button>
            </div>

            <!-- Filter Kategori -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none text-xs font-medium">
                <button
                    @click="selectedCategory = 'all'"
                    :class="['px-3 py-1.5 rounded-full whitespace-nowrap transition', selectedCategory === 'all' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200']"
                >
                    Semua ({{ documents.length }})
                </button>
                <button
                    @click="selectedCategory = 'vendor_contract'"
                    :class="['px-3 py-1.5 rounded-full whitespace-nowrap transition', selectedCategory === 'vendor_contract' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200']"
                >
                    Kontrak Vendor
                </button>
                <button
                    @click="selectedCategory = 'invoice'"
                    :class="['px-3 py-1.5 rounded-full whitespace-nowrap transition', selectedCategory === 'invoice' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200']"
                >
                    Invoice / Bukti Bayar
                </button>
                <button
                    @click="selectedCategory = 'legal_kua'"
                    :class="['px-3 py-1.5 rounded-full whitespace-nowrap transition', selectedCategory === 'legal_kua' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200']"
                >
                    Berkas Legal KUA / Sipil
                </button>
                <button
                    @click="selectedCategory = 'rundown'"
                    :class="['px-3 py-1.5 rounded-full whitespace-nowrap transition', selectedCategory === 'rundown' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200']"
                >
                    Rundown & Petunjuk
                </button>
            </div>

            <!-- Documents Grid -->
            <div v-if="filteredDocs.length === 0" class="text-center py-12 border border-dashed rounded-xl dark:border-neutral-800">
                <p class="text-neutral-500 text-sm">Belum ada dokumen yang diunggah.</p>
                <button @click="isUploadModalOpen = true" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    Unggah dokumen pertama
                </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="doc in filteredDocs"
                    :key="doc.id"
                    class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm flex flex-col justify-between space-y-4"
                >
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <div class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
                                    <FileText class="w-5 h-5" />
                                </div>
                                <div>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">
                                        {{ getCategoryLabel(doc.category) }}
                                    </span>
                                    <h3 class="font-semibold text-sm leading-tight text-neutral-900 dark:text-neutral-100">
                                        {{ doc.title }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="text-xs text-neutral-500 space-y-0.5 pt-1">
                            <div>Nama file: <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ doc.file_name }}</span></div>
                            <div>Ukuran: {{ formatBytes(doc.file_size_bytes) }}</div>
                            <div v-if="doc.vendor">Vendor: <span class="font-medium">{{ doc.vendor.name }}</span></div>
                            <div v-if="doc.notes" class="text-[11px] bg-neutral-50 dark:bg-neutral-800/50 p-2 rounded mt-2">
                                {{ doc.notes }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t dark:border-neutral-800">
                        <a
                            :href="`${$page.url}/${doc.id}/download`"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            <Download class="w-3.5 h-3.5" /> Unduh File
                        </a>
                        <button
                            @click="deleteDocument(doc)"
                            class="p-1.5 text-rose-500 hover:text-rose-700 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Checklist Dokumen per Agama/Adat -->
            <div v-if="checklist && checklist.length > 0" class="mt-8 p-5 border rounded-xl bg-amber-50/50 dark:bg-amber-950/20 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            📋 Checklist Dokumen Pernikahan
                        </h2>
                        <p class="text-xs text-neutral-500 mt-1">
                            Daftar berkas berdasarkan agama <span class="font-semibold capitalize">{{ weddingProject.religion || 'umum' }}</span>
                            <span v-if="weddingProject.tradition"> &amp; adat <span class="font-semibold capitalize">{{ weddingProject.tradition }}</span></span>.
                        </p>
                    </div>
                    <span class="text-[10px] font-medium px-2 py-1 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200 whitespace-nowrap">
                        v{{ checklist[0].version }}
                    </span>
                </div>

                <div class="bg-amber-100/60 dark:bg-amber-950/30 rounded-lg p-3 mb-4">
                    <p class="text-[11px] text-amber-800 dark:text-amber-200 leading-relaxed">
                        ⚠️ {{ checklist[0]?.version_note || 'Persyaratan dapat berbeda menurut daerah dan berubah seiring waktu. Selalu konfirmasi ke instansi terkait setempat.' }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label
                        v-for="item in checklist"
                        :key="item.id"
                        class="flex items-start gap-3 p-3 rounded-lg bg-white dark:bg-neutral-900 border dark:border-neutral-800 hover:border-amber-300 dark:hover:border-amber-700 transition cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            class="mt-0.5 w-4 h-4 rounded border-neutral-300 text-amber-600 focus:ring-amber-500"
                        />
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-neutral-400">{{ item.sort_order }}</span>
                                <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ item.title }}</span>
                            </div>
                            <p v-if="item.description" class="text-xs text-neutral-500 mt-0.5">{{ item.description }}</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div v-if="isUploadModalOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-lg">Unggah Dokumen Baru</h3>
                    <button @click="isUploadModalOpen = false" class="text-neutral-400 hover:text-neutral-600">✕</button>
                </div>

                <form @submit.prevent="submitDocument" class="space-y-4 text-sm">
                    <div>
                        <label class="block font-medium mb-1">Pilih File (PDF, Foto, Dokumen)</label>
                        <input
                            type="file"
                            required
                            @change="handleFileChange"
                            class="w-full text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-neutral-900 file:text-white dark:file:bg-neutral-100 dark:file:text-neutral-900 hover:file:opacity-90"
                        />
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Judul Dokumen</label>
                        <input
                            v-model="docForm.title"
                            type="text"
                            required
                            placeholder="Contoh: Kontrak Plataran Catering 2026"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Kategori Dokumen</label>
                        <select
                            v-model="docForm.category"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        >
                            <option value="vendor_contract">Kontrak / MOU Vendor</option>
                            <option value="invoice">Invoice / Kwitansi / Bukti Bayar</option>
                            <option value="legal_kua">Berkas Legal KUA</option>
                            <option value="legal_church">Berkas Legal Catatan Sipil/Gereja</option>
                            <option value="rundown">Rundown & Panduan Teknis</option>
                            <option value="general">Umum / Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Terkait Vendor (Opsional)</label>
                        <select
                            v-model="docForm.vendor_id"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        >
                            <option :value="null">-- Tidak Terkait Vendor Khusus --</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea
                            v-model="docForm.notes"
                            rows="2"
                            placeholder="Keterangan isi berkas atau nomor ref..."
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="isUploadModalOpen = false"
                            class="px-4 py-2 border rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="docForm.processing"
                            class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 font-medium rounded-lg hover:opacity-90 disabled:opacity-50"
                        >
                            Unggah Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
