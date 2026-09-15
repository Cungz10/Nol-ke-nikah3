<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import TeamInvitationAlert from '@/components/TeamInvitationAlert.vue';
import { CheckCircle2, Clock, AlertTriangle, Users, Calendar, DollarSign, ArrowRight, Phone } from '@lucide/vue';
import type { BreadcrumbItem } from '@/types';

interface PendingInvitation {
    code: string;
    inviterName: string;
    team: {
        name: string;
        slug: string;
    };
}

interface Task {
    id: number;
    title: string;
    is_critical: boolean;
    due_date?: string;
    status: string;
}

interface Vendor {
    id: number;
    name: string;
    phone?: string;
    status: string;
    follow_up_date?: string;
    vendor_category?: { name: string };
}

const props = defineProps<{
    pendingInvitations?: PendingInvitation[];
    weddingProject: {
        id: number;
        name: string;
        target_date?: string;
        partner_one_name: string;
        partner_two_name?: string;
        city?: string;
        religion?: string;
        estimated_guests?: number;
    };
    metrics: {
        totalTasks: number;
        completedTasks: number;
        overdueTasksCount: number;
        progressPercentage: number;
        totalBudget: number;
        totalSpent: number;
        remainingBudget: number;
    };
    urgentTasks: Task[];
    vendorsToFollowUp: Vendor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
];

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug || '');

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
};

const daysUntilWedding = computed(() => {
    if (!props.weddingProject.target_date) return null;
    const target = new Date(props.weddingProject.target_date);
    const today = new Date();
    const diffTime = target.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dashboard Ringkasan" />

        <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
            <!-- Undangan Masuk / Pending -->
            <TeamInvitationAlert
                v-if="pendingInvitations && pendingInvitations.length > 0"
                :invitations="pendingInvitations"
            />

            <!-- Hero Welcome Card -->
            <div v-if="weddingProject" class="p-6 rounded-2xl bg-gradient-to-r from-neutral-900 to-neutral-800 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs uppercase tracking-wider text-neutral-400 font-semibold">Wedding Workspace</span>
                    <h1 class="text-2xl md:text-3xl font-bold">
                        {{ weddingProject.partner_one_name }} {{ weddingProject.partner_two_name ? '& ' + weddingProject.partner_two_name : '' }}
                    </h1>
                    <p class="text-sm text-neutral-300 flex items-center gap-3">
                        <span v-if="weddingProject.target_date">📅 {{ weddingProject.target_date }}</span>
                        <span v-if="weddingProject.city">📍 {{ weddingProject.city }}</span>
                        <span v-if="weddingProject.estimated_guests">👥 ~{{ weddingProject.estimated_guests }} Tamu</span>
                    </p>
                </div>

                <div v-if="daysUntilWedding !== null" class="bg-white/10 backdrop-blur px-5 py-3 rounded-xl text-center self-start md:self-auto">
                    <div class="text-2xl md:text-3xl font-black">{{ daysUntilWedding }}</div>
                    <div class="text-[11px] uppercase tracking-wider text-neutral-300">Hari Menuju Hari-H</div>
                </div>
            </div>

            <!-- Empty Project Prompt if not onboarded -->
            <div v-else class="p-6 border rounded-2xl bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-lg text-amber-900 dark:text-amber-100">Project Pernikahan Belum Dikonfigurasi</h2>
                    <p class="text-sm text-amber-700 dark:text-amber-300">Mulai onboarding untuk membuat roadmap pernikahan dan alokasi budget.</p>
                </div>
                <Link :href="`/${teamSlug}/onboarding`" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg text-sm">
                    Mulai Onboarding
                </Link>
            </div>

            <!-- Top Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Checklist Progress -->
                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-neutral-400">Progress Checklist</span>
                        <Calendar class="w-4 h-4 text-indigo-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ metrics.progressPercentage }}%</div>
                        <div class="text-xs text-neutral-500">{{ metrics.completedTasks }}/{{ metrics.totalTasks }} Tugas</div>
                    </div>
                    <div class="w-full bg-neutral-100 dark:bg-neutral-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full" :style="{ width: `${metrics.progressPercentage}%` }"></div>
                    </div>
                </div>

                <!-- Budget Overview -->
                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-neutral-400">Realisasi Budget</span>
                        <DollarSign class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-xl font-bold">{{ formatRupiah(metrics.totalSpent) }}</div>
                        <div class="text-xs text-neutral-500">dari {{ formatRupiah(metrics.totalBudget) }}</div>
                    </div>
                    <div class="text-xs" :class="metrics.remainingBudget < 0 ? 'text-rose-500 font-semibold' : 'text-neutral-500'">
                        Sisa: {{ formatRupiah(metrics.remainingBudget) }}
                    </div>
                </div>

                <!-- Task Terlewat Alert -->
                <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase text-neutral-400">Perhatian / Alert</span>
                        <AlertTriangle class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="text-2xl font-bold" :class="metrics.overdueTasksCount > 0 ? 'text-rose-600' : 'text-emerald-600'">
                        {{ metrics.overdueTasksCount }} Tugas
                    </div>
                    <div class="text-xs text-neutral-500">
                        {{ metrics.overdueTasksCount > 0 ? 'Melewati batas deadline target' : 'Semua deadline tugas terkendali' }}
                    </div>
                </div>
            </div>

            <!-- 2-Column Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Urgent Tasks -->
                <div class="border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-4 border-b dark:border-neutral-800 flex items-center justify-between">
                            <h3 class="font-bold text-base">Tugas Mendesak & Prioritas</h3>
                            <Link :href="`/${teamSlug}/timeline`" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1 hover:underline">
                                Lihat Semua <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        <div v-if="urgentTasks.length === 0" class="p-8 text-center text-xs text-neutral-400">
                            Tidak ada tugas mendesak saat ini.
                        </div>

                        <div v-else class="divide-y dark:divide-neutral-800">
                            <div v-for="task in urgentTasks" :key="task.id" class="p-4 flex items-start justify-between gap-3 text-sm">
                                <div class="space-y-0.5">
                                    <div class="font-medium flex items-center gap-2">
                                        <span>{{ task.title }}</span>
                                        <span v-if="task.is_critical" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                            KRUSIAL
                                        </span>
                                    </div>
                                    <div v-if="task.due_date" class="text-xs text-neutral-400">
                                        Deadline: {{ task.due_date }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Follow Ups -->
                <div class="border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-4 border-b dark:border-neutral-800 flex items-center justify-between">
                            <h3 class="font-bold text-base">Jadwal Follow Up Vendor</h3>
                            <Link :href="`/${teamSlug}/vendors`" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1 hover:underline">
                                Kelola Vendor <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        <div v-if="vendorsToFollowUp.length === 0" class="p-8 text-center text-xs text-neutral-400">
                            Tidak ada agenda follow-up vendor dalam waktu dekat.
                        </div>

                        <div v-else class="divide-y dark:divide-neutral-800">
                            <div v-for="v in vendorsToFollowUp" :key="v.id" class="p-4 flex items-center justify-between text-sm">
                                <div class="space-y-0.5">
                                    <div class="font-medium">{{ v.name }}</div>
                                    <div class="text-xs text-neutral-400">
                                        {{ v.vendor_category?.name }} • Follow up: {{ v.follow_up_date }}
                                    </div>
                                </div>
                                <div v-if="v.phone" class="flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                    <Phone class="w-3.5 h-3.5" /> {{ v.phone }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
