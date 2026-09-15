<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2, DollarSign, PieChart, CreditCard, AlertTriangle, CheckCircle2 } from '@lucide/vue';
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
    wedding_project_id: number;
    vendor_id?: number;
    budget_allocation_id?: number;
    title: string;
    amount_idr: number;
    payment_type: string;
    payment_date: string;
    notes?: string;
    vendor?: { id: number; name: string };
    budget_allocation?: { id: number; vendor_category_id: number };
}

interface BudgetAllocation {
    id: number;
    wedding_project_id: number;
    vendor_category_id: number;
    percentage: number;
    allocated_amount_idr: number;
    notes?: string;
    vendor_category?: VendorCategory;
    payments?: Payment[];
}

interface Vendor {
    id: number;
    name: string;
    vendor_category_id: number;
}

const props = defineProps<{
    weddingProject: { id: number; name: string; budget_total: number };
    categories: VendorCategory[];
    allocations: BudgetAllocation[];
    payments: Payment[];
    vendors: Vendor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
    { title: 'Budget & Keuangan', href: '#' },
];

const isPaymentModalOpen = ref(false);
const isAllocationModalOpen = ref(false);

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const totalAllocated = computed(() => {
    return props.allocations.reduce((sum, item) => sum + item.allocated_amount_idr, 0);
});

const totalSpent = computed(() => {
    return props.payments.reduce((sum, item) => sum + item.amount_idr, 0);
});

const remainingBudget = computed(() => {
    return props.weddingProject.budget_total - totalSpent.value;
});

const totalPercentage = computed(() => {
    return props.allocations.reduce((sum, item) => sum + item.percentage, 0);
});

// Per-category calculated spending
const getSpentByCategory = (catId: number) => {
    return props.payments
        .filter(p => {
            if (p.budget_allocation?.vendor_category_id === catId) return true;
            if (p.vendor?.id) {
                const v = props.vendors.find(item => item.id === p.vendor?.id);
                return v?.vendor_category_id === catId;
            }
            return false;
        })
        .reduce((sum, p) => sum + p.amount_idr, 0);
};

// Form Payment
const paymentForm = useForm({
    vendor_id: null as number | null,
    budget_allocation_id: null as number | null,
    title: '',
    amount_idr: 0,
    payment_type: 'dp',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

// Form Allocation
const allocationForm = useForm({
    allocations: props.allocations.map(a => ({
        vendor_category_id: a.vendor_category_id,
        percentage: a.percentage,
        allocated_amount_idr: a.allocated_amount_idr,
        notes: a.notes || '',
    })),
});

const updateCalculatedAmounts = () => {
    allocationForm.allocations.forEach(a => {
        a.allocated_amount_idr = Math.round((props.weddingProject.budget_total * a.percentage) / 100);
    });
};

const submitPayment = () => {
    paymentForm.post(window.location.pathname + '/payments', {
        onSuccess: () => {
            isPaymentModalOpen.value = false;
            paymentForm.reset();
        },
    });
};

const submitAllocations = () => {
    allocationForm.post(window.location.pathname + '/allocations', {
        onSuccess: () => {
            isAllocationModalOpen.value = false;
        },
    });
};

const deletePayment = (payment: Payment) => {
    if (confirm(`Yakin hapus transaksi ${payment.title}?`)) {
        useForm({}).delete(window.location.pathname + '/payments/' + payment.id);
    }
};

const getPaymentTypeLabel = (type: string) => {
    const map: Record<string, string> = {
        dp: 'Uang Muka (DP)',
        installment: 'Cicilan / Termin',
        full: 'Pelunasan',
        manual_expense: 'Pengeluaran Langsung',
    };
    return map[type] || type;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Budget & Keuangan" />

        <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Budget & Keuangan</h1>
                    <p class="text-sm text-neutral-500">Monitor rencana alokasi dan realisasi pengeluaran pernikahan</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="isAllocationModalOpen = true"
                        class="px-4 py-2 border dark:border-neutral-800 rounded-lg text-sm font-medium hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                    >
                        Atur Alokasi %
                    </button>
                    <button
                        @click="isPaymentModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 rounded-lg text-sm font-medium hover:opacity-90 transition"
                    >
                        <Plus class="w-4 h-4" />
                        Catat Pengeluaran
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase text-neutral-400">Total Anggaran</span>
                    <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ formatRupiah(weddingProject.budget_total) }}
                    </div>
                    <div class="text-[11px] text-neutral-500">Target budget pernikahan</div>
                </div>

                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase text-neutral-400">Total Teralokasi</span>
                    <div class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ formatRupiah(totalAllocated) }}
                    </div>
                    <div class="text-[11px] text-neutral-500">{{ totalPercentage }}% dari total anggaran</div>
                </div>

                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase text-neutral-400">Realisasi Terpakai</span>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ formatRupiah(totalSpent) }}
                    </div>
                    <div class="text-[11px] text-neutral-500">
                        {{ Math.round((totalSpent / (weddingProject.budget_total || 1)) * 100) }}% terpakai
                    </div>
                </div>

                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-1">
                    <span class="text-xs font-semibold uppercase text-neutral-400">Sisa Anggaran</span>
                    <div :class="['text-xl font-bold', remainingBudget < 0 ? 'text-rose-600' : 'text-neutral-900 dark:text-neutral-100']">
                        {{ formatRupiah(remainingBudget) }}
                    </div>
                    <div class="text-[11px]" :class="remainingBudget < 0 ? 'text-rose-500 font-medium' : 'text-neutral-500'">
                        {{ remainingBudget < 0 ? 'Over budget!' : 'Budget aman' }}
                    </div>
                </div>
            </div>

            <!-- Detail Alokasi & Pengeluaran Per Kategori -->
            <div class="border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="p-4 border-b dark:border-neutral-800 flex items-center justify-between">
                    <h3 class="font-bold text-base">Alokasi & Penggunaan per Kategori</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-800/50 text-neutral-500 text-xs uppercase font-medium">
                            <tr>
                                <th class="p-4">Kategori Vendor</th>
                                <th class="p-4 text-center">Porsi (%)</th>
                                <th class="p-4 text-right">Rencana Anggaran</th>
                                <th class="p-4 text-right">Realisasi (Terbayar)</th>
                                <th class="p-4 text-right">Sisa Alokasi</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-neutral-800">
                            <tr v-for="alloc in allocations" :key="alloc.id" class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition">
                                <td class="p-4 font-medium">{{ alloc.vendor_category?.name }}</td>
                                <td class="p-4 text-center font-semibold text-neutral-500">{{ alloc.percentage }}%</td>
                                <td class="p-4 text-right">{{ formatRupiah(alloc.allocated_amount_idr) }}</td>
                                <td class="p-4 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                    {{ formatRupiah(getSpentByCategory(alloc.vendor_category_id)) }}
                                </td>
                                <td class="p-4 text-right" :class="alloc.allocated_amount_idr - getSpentByCategory(alloc.vendor_category_id) < 0 ? 'text-rose-500 font-semibold' : ''">
                                    {{ formatRupiah(alloc.allocated_amount_idr - getSpentByCategory(alloc.vendor_category_id)) }}
                                </td>
                                <td class="p-4 text-center">
                                    <span
                                        v-if="getSpentByCategory(alloc.vendor_category_id) > alloc.allocated_amount_idr"
                                        class="px-2 py-0.5 rounded text-[11px] font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 inline-flex items-center gap-1"
                                    >
                                        <AlertTriangle class="w-3 h-3" /> Over
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 inline-flex items-center gap-1"
                                    >
                                        <CheckCircle2 class="w-3 h-3" /> Aman
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Pembayaran / Transaksi -->
            <div class="border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm overflow-hidden">
                <div class="p-4 border-b dark:border-neutral-800 flex items-center justify-between">
                    <h3 class="font-bold text-base">Riwayat Transaksi & Pembayaran</h3>
                    <span class="text-xs text-neutral-500">{{ payments.length }} transaksi tercatat</span>
                </div>

                <div v-if="payments.length === 0" class="p-8 text-center text-neutral-500 text-sm">
                    Belum ada riwayat transaksi pengeluaran.
                </div>

                <div v-else class="divide-y dark:divide-neutral-800">
                    <div
                        v-for="payment in payments"
                        :key="payment.id"
                        class="p-4 flex items-center justify-between hover:bg-neutral-50/50 dark:hover:bg-neutral-800/30 transition text-sm"
                    >
                        <div class="space-y-0.5">
                            <div class="font-semibold flex items-center gap-2">
                                <span>{{ payment.title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                                    {{ getPaymentTypeLabel(payment.payment_type) }}
                                </span>
                            </div>
                            <div class="text-xs text-neutral-500 flex items-center gap-3">
                                <span>Tgl: {{ payment.payment_date }}</span>
                                <span v-if="payment.vendor">• Vendor: {{ payment.vendor.name }}</span>
                                <span v-if="payment.notes">• {{ payment.notes }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="font-bold text-base text-neutral-900 dark:text-neutral-100">
                                {{ formatRupiah(payment.amount_idr) }}
                            </div>
                            <button
                                @click="deletePayment(payment)"
                                class="p-1.5 text-rose-500 hover:text-rose-700 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Pembayaran -->
        <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-lg">Catat Pengeluaran / Pembayaran</h3>
                    <button @click="isPaymentModalOpen = false" class="text-neutral-400 hover:text-neutral-600">✕</button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4 text-sm">
                    <div>
                        <label class="block font-medium mb-1">Judul / Keterangan Transaksi</label>
                        <input
                            v-model="paymentForm.title"
                            type="text"
                            required
                            placeholder="Contoh: DP Gedung 30%"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Nominal (IDR)</label>
                        <input
                            v-model.number="paymentForm.amount_idr"
                            type="number"
                            required
                            min="1"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1">Jenis Pembayaran</label>
                            <select
                                v-model="paymentForm.payment_type"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            >
                                <option value="dp">Uang Muka (DP)</option>
                                <option value="installment">Cicilan / Termin</option>
                                <option value="full">Pelunasan</option>
                                <option value="manual_expense">Pengeluaran Langsung</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Tanggal Transaksi</label>
                            <input
                                v-model="paymentForm.payment_date"
                                type="date"
                                required
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Terkait Vendor (Opsional)</label>
                        <select
                            v-model="paymentForm.vendor_id"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        >
                            <option :value="null">-- Bukan Vendor / Umum --</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Catatan Tambahan</label>
                        <textarea
                            v-model="paymentForm.notes"
                            rows="2"
                            placeholder="Transfer via BCA, no ref: ..."
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="isPaymentModalOpen = false"
                            class="px-4 py-2 border rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 font-medium rounded-lg hover:opacity-90 disabled:opacity-50"
                        >
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Atur Alokasi Budget -->
        <div v-if="isAllocationModalOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl my-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-lg">Atur Persentase Alokasi Budget</h3>
                        <p class="text-xs text-neutral-500">Sesuaikan target porsi budget per kategori vendor</p>
                    </div>
                    <button @click="isAllocationModalOpen = false" class="text-neutral-400 hover:text-neutral-600">✕</button>
                </div>

                <form @submit.prevent="submitAllocations" class="space-y-3 text-sm max-h-[60vh] overflow-y-auto pr-1">
                    <div
                        v-for="(item, idx) in allocationForm.allocations"
                        :key="item.vendor_category_id"
                        class="flex items-center justify-between gap-3 p-2.5 border rounded-lg dark:border-neutral-800"
                    >
                        <span class="font-medium text-xs flex-1">
                            {{ categories.find(c => c.id === item.vendor_category_id)?.name }}
                        </span>
                        <div class="flex items-center gap-2 w-32">
                            <input
                                v-model.number="item.percentage"
                                type="number"
                                min="0"
                                max="100"
                                @input="updateCalculatedAmounts"
                                class="w-16 px-2 py-1 border rounded text-right bg-transparent dark:border-neutral-800 text-xs font-semibold"
                            />
                            <span class="text-xs text-neutral-400">%</span>
                        </div>
                        <div class="w-28 text-right text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                            {{ formatRupiah(item.allocated_amount_idr) }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t dark:border-neutral-800 sticky bottom-0 bg-white dark:bg-neutral-900">
                        <button
                            type="button"
                            @click="isAllocationModalOpen = false"
                            class="px-4 py-2 border rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="allocationForm.processing"
                            class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 font-medium rounded-lg hover:opacity-90 disabled:opacity-50"
                        >
                            Simpan Alokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
