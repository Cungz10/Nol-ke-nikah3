<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2, Edit2, Phone, Mail, Globe, Calendar } from '@lucide/vue';
import type { BreadcrumbItem } from '@/types';

interface VendorCategory {
    id: number;
    name: string;
    slug: string;
    icon: string;
    default_percentage: number;
}

interface Payment {
    id: number;
    title: string;
    amount_idr: number;
    payment_type: string;
    payment_date: string;
}

interface Vendor {
    id: number;
    wedding_project_id: number;
    vendor_category_id: number;
    name: string;
    contact_person?: string;
    phone?: string;
    email?: string;
    instagram?: string;
    website?: string;
    quote_amount_idr: number;
    deal_amount_idr: number;
    status: string;
    notes?: string;
    follow_up_date?: string;
    vendor_category?: VendorCategory;
    payments?: Payment[];
}

const props = defineProps<{
    weddingProject: { id: number; name: string; target_date?: string };
    categories: VendorCategory[];
    vendors: Vendor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
    { title: 'Vendors', href: '#' },
];

const selectedCategory = ref<number | null>(null);
const isAddModalOpen = ref(false);
const editingVendor = ref<Vendor | null>(null);

const filteredVendors = computed(() => {
    if (!selectedCategory.value) return props.vendors;
    return props.vendors.filter(v => v.vendor_category_id === selectedCategory.value);
});

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const vendorForm = useForm({
    vendor_category_id: props.categories[0]?.id || 1,
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    instagram: '',
    website: '',
    quote_amount_idr: 0,
    deal_amount_idr: 0,
    status: 'research',
    notes: '',
    follow_up_date: '',
});

const openAddModal = () => {
    editingVendor.value = null;
    vendorForm.reset();
    vendorForm.vendor_category_id = selectedCategory.value || props.categories[0]?.id || 1;
    isAddModalOpen.value = true;
};

const openEditModal = (vendor: Vendor) => {
    editingVendor.value = vendor;
    vendorForm.vendor_category_id = vendor.vendor_category_id;
    vendorForm.name = vendor.name;
    vendorForm.contact_person = vendor.contact_person || '';
    vendorForm.phone = vendor.phone || '';
    vendorForm.email = vendor.email || '';
    vendorForm.instagram = vendor.instagram || '';
    vendorForm.website = vendor.website || '';
    vendorForm.quote_amount_idr = vendor.quote_amount_idr;
    vendorForm.deal_amount_idr = vendor.deal_amount_idr;
    vendorForm.status = vendor.status;
    vendorForm.notes = vendor.notes || '';
    vendorForm.follow_up_date = vendor.follow_up_date || '';
    isAddModalOpen.value = true;
};

const submitVendor = () => {
    if (editingVendor.value) {
        vendorForm.put(window.location.pathname + '/' + editingVendor.value.id, {
            onSuccess: () => {
                isAddModalOpen.value = false;
                vendorForm.reset();
            },
        });
    } else {
        vendorForm.post(window.location.pathname, {
            onSuccess: () => {
                isAddModalOpen.value = false;
                vendorForm.reset();
            },
        });
    }
};

const deleteVendor = (vendor: Vendor) => {
    if (confirm(`Yakin hapus vendor ${vendor.name}?`)) {
        useForm({}).delete(window.location.pathname + '/' + vendor.id);
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'research': return 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300';
        case 'negotiation': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
        case 'booked': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
        case 'dp_paid': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300';
        case 'fully_paid': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
        case 'cancelled': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300';
        default: return 'bg-neutral-100 text-neutral-700';
    }
};

const getStatusLabel = (status: string) => {
    const map: Record<string, string> = {
        research: 'Riset',
        negotiation: 'Nego',
        booked: 'Booked',
        dp_paid: 'DP Lunas',
        fully_paid: 'Lunas Total',
        cancelled: 'Batal',
    };
    return map[status] || status;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Vendor Management" />

        <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Vendor Management</h1>
                    <p class="text-sm text-neutral-500">Kelola dan pantau vendor pernikahan {{ weddingProject.name }}</p>
                </div>
                <button
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 rounded-lg text-sm font-medium hover:opacity-90 transition"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Vendor
                </button>
            </div>

            <!-- Filter Kategori -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button
                    @click="selectedCategory = null"
                    :class="[
                        'px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition',
                        selectedCategory === null
                            ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                            : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200'
                    ]"
                >
                    Semua ({{ vendors.length }})
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCategory = cat.id"
                    :class="[
                        'px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition',
                        selectedCategory === cat.id
                            ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900'
                            : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200'
                    ]"
                >
                    {{ cat.name }} ({{ vendors.filter(v => v.vendor_category_id === cat.id).length }})
                </button>
            </div>

            <!-- List Vendor -->
            <div v-if="filteredVendors.length === 0" class="text-center py-12 border border-dashed rounded-xl dark:border-neutral-800">
                <p class="text-neutral-500 text-sm">Belum ada vendor di kategori ini.</p>
                <button @click="openAddModal" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    Tambah vendor pertama
                </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="vendor in filteredVendors"
                    :key="vendor.id"
                    class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 flex flex-col justify-between space-y-4 shadow-sm"
                >
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-neutral-400">
                                    {{ vendor.vendor_category?.name }}
                                </span>
                                <h3 class="font-semibold text-base leading-tight text-neutral-900 dark:text-neutral-100">
                                    {{ vendor.name }}
                                </h3>
                            </div>
                            <span :class="['px-2 py-0.5 rounded text-[11px] font-medium', getStatusBadgeClass(vendor.status)]">
                                {{ getStatusLabel(vendor.status) }}
                            </span>
                        </div>

                        <!-- Info kontak -->
                        <div class="space-y-1 text-xs text-neutral-600 dark:text-neutral-400 pt-1">
                            <div v-if="vendor.contact_person" class="font-medium">PIC: {{ vendor.contact_person }}</div>
                            <div v-if="vendor.phone" class="flex items-center gap-1.5">
                                <Phone class="w-3.5 h-3.5 text-neutral-400" />
                                <span>{{ vendor.phone }}</span>
                            </div>
                            <div v-if="vendor.email" class="flex items-center gap-1.5">
                                <Mail class="w-3.5 h-3.5 text-neutral-400" />
                                <span>{{ vendor.email }}</span>
                            </div>
                            <div v-if="vendor.instagram" class="flex items-center gap-1.5">
                                <span class="font-bold text-neutral-400">IG:</span>
                                <span>@{{ vendor.instagram }}</span>
                            </div>
                        </div>

                        <!-- Harga & Catatan -->
                        <div class="pt-2 border-t dark:border-neutral-800 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-neutral-400">Quote:</span>
                                <div class="font-semibold text-neutral-700 dark:text-neutral-300">
                                    {{ formatRupiah(vendor.quote_amount_idr) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-neutral-400">Deal:</span>
                                <div class="font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ formatRupiah(vendor.deal_amount_idr) }}
                                </div>
                            </div>
                        </div>

                        <div v-if="vendor.notes" class="text-xs text-neutral-500 bg-neutral-50 dark:bg-neutral-800/50 p-2 rounded">
                            {{ vendor.notes }}
                        </div>

                        <div v-if="vendor.follow_up_date" class="text-[11px] text-amber-600 dark:text-amber-400 flex items-center gap-1">
                            <Calendar class="w-3 h-3" />
                            Follow up: {{ vendor.follow_up_date }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t dark:border-neutral-800">
                        <button
                            @click="openEditModal(vendor)"
                            class="p-1.5 text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                        >
                            <Edit2 class="w-4 h-4" />
                        </button>
                        <button
                            @click="deleteVendor(vendor)"
                            class="p-1.5 text-rose-500 hover:text-rose-700 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl my-8">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-lg">
                        {{ editingVendor ? 'Edit Vendor' : 'Tambah Vendor Baru' }}
                    </h3>
                    <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-600">✕</button>
                </div>

                <form @submit.prevent="submitVendor" class="space-y-4 text-sm">
                    <div>
                        <label class="block font-medium mb-1">Kategori Vendor</label>
                        <select
                            v-model="vendorForm.vendor_category_id"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Nama Vendor / Bisnis</label>
                        <input
                            v-model="vendorForm.name"
                            type="text"
                            required
                            placeholder="Contoh: Plataran Catering"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1">PIC / Kontak</label>
                            <input
                                v-model="vendorForm.contact_person"
                                type="text"
                                placeholder="Nama PIC"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                        <div>
                            <label class="block font-medium mb-1">No. WhatsApp / HP</label>
                            <input
                                v-model="vendorForm.phone"
                                type="text"
                                placeholder="0812..."
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1">Instagram</label>
                            <input
                                v-model="vendorForm.instagram"
                                type="text"
                                placeholder="username_tanpa_@"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Status Progres</label>
                            <select
                                v-model="vendorForm.status"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            >
                                <option value="research">Riset</option>
                                <option value="negotiation">Nego</option>
                                <option value="booked">Booked</option>
                                <option value="dp_paid">DP Lunas</option>
                                <option value="fully_paid">Lunas Total</option>
                                <option value="cancelled">Batal</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1">Penawaran (Quote IDR)</label>
                            <input
                                v-model.number="vendorForm.quote_amount_idr"
                                type="number"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Kesepakatan (Deal IDR)</label>
                            <input
                                v-model.number="vendorForm.deal_amount_idr"
                                type="number"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Tanggal Follow Up Berikutnya</label>
                        <input
                            v-model="vendorForm.follow_up_date"
                            type="date"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Catatan Tambahan</label>
                        <textarea
                            v-model="vendorForm.notes"
                            rows="2"
                            placeholder="Detail paket, bonus, ketentuan khusus..."
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="isAddModalOpen = false"
                            class="px-4 py-2 border rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="vendorForm.processing"
                            class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 font-medium rounded-lg hover:opacity-90 disabled:opacity-50"
                        >
                            {{ editingVendor ? 'Simpan Perubahan' : 'Tambah Vendor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
