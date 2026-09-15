<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Plus, Trash2, Edit2, CheckCircle, Circle, Clock, AlertCircle } from '@lucide/vue';
import type { BreadcrumbItem } from '@/types';

interface Task {
    id: number;
    timeline_phase_id: number;
    title: string;
    description?: string;
    sort_order: number;
    is_critical: boolean;
    due_date?: string;
    status: 'todo' | 'in_progress' | 'completed';
}

interface TimelinePhase {
    id: number;
    wedding_project_id: number;
    key: string;
    name: string;
    sort_order: number;
    starts_on?: string;
    ends_on?: string;
    tasks: Task[];
}

const props = defineProps<{
    weddingProject: { id: number; name: string; target_date?: string };
    phases: TimelinePhase[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '#' },
    { title: 'Timeline & Checklist', href: '#' },
];

const selectedFilter = ref<'all' | 'critical' | 'overdue' | 'pending'>('all');
const isTaskModalOpen = ref(false);
const editingTask = ref<Task | null>(null);

const todayStr = new Date().toISOString().split('T')[0];

const isOverdue = (task: Task) => {
    return task.status !== 'completed' && task.due_date && task.due_date < todayStr;
};

// Summary metrics
const allTasks = computed(() => props.phases.flatMap(p => p.tasks));
const totalTasks = computed(() => allTasks.value.length);
const completedTasks = computed(() => allTasks.value.filter(t => t.status === 'completed').length);
const overdueTasks = computed(() => allTasks.value.filter(isOverdue).length);
const progressPercentage = computed(() => {
    if (totalTasks.value === 0) return 0;
    return Math.round((completedTasks.value / totalTasks.value) * 100);
});

// Form Task
const taskForm = useForm({
    timeline_phase_id: props.phases[0]?.id || 1,
    title: '',
    description: '',
    is_critical: false,
    due_date: '',
    status: 'todo' as 'todo' | 'in_progress' | 'completed',
});

const openAddTask = (phaseId?: number) => {
    editingTask.value = null;
    taskForm.reset();
    if (phaseId) taskForm.timeline_phase_id = phaseId;
    isTaskModalOpen.value = true;
};

const openEditTask = (task: Task) => {
    editingTask.value = task;
    taskForm.timeline_phase_id = task.timeline_phase_id;
    taskForm.title = task.title;
    taskForm.description = task.description || '';
    taskForm.is_critical = Boolean(task.is_critical);
    taskForm.due_date = task.due_date || '';
    taskForm.status = task.status;
    isTaskModalOpen.value = true;
};

const submitTask = () => {
    if (editingTask.value) {
        taskForm.put(window.location.pathname + '/tasks/' + editingTask.value.id, {
            onSuccess: () => {
                isTaskModalOpen.value = false;
                taskForm.reset();
            },
        });
    } else {
        taskForm.post(window.location.pathname + '/tasks', {
            onSuccess: () => {
                isTaskModalOpen.value = false;
                taskForm.reset();
            },
        });
    }
};

const toggleStatus = (task: Task) => {
    const nextStatus: Record<string, 'todo' | 'in_progress' | 'completed'> = {
        todo: 'in_progress',
        in_progress: 'completed',
        completed: 'todo',
    };
    useForm({
        status: nextStatus[task.status] || 'todo',
    }).put(window.location.pathname + '/tasks/' + task.id);
};

const deleteTask = (task: Task) => {
    if (confirm(`Hapus tugas "${task.title}"?`)) {
        useForm({}).delete(window.location.pathname + '/tasks/' + task.id);
    }
};

const filterTask = (task: Task) => {
    if (selectedFilter.value === 'critical') return task.is_critical;
    if (selectedFilter.value === 'overdue') return isOverdue(task);
    if (selectedFilter.value === 'pending') return task.status !== 'completed';
    return true;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Timeline & Roadmap" />

        <div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Timeline & Checklist Persiapan</h1>
                    <p class="text-sm text-neutral-500">
                        Roadmap bertahap pernikahan {{ weddingProject.name }}
                        <span v-if="weddingProject.target_date" class="font-medium text-neutral-800 dark:text-neutral-200">
                            • Hari-H: {{ weddingProject.target_date }}
                        </span>
                    </p>
                </div>
                <button
                    @click="openAddTask()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 rounded-lg text-sm font-medium hover:opacity-90 transition"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Tugas
                </button>
            </div>

            <!-- Progress Bar Card -->
            <div class="p-5 border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="space-y-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-neutral-400">Progress Checklist</span>
                        <div class="text-xl font-bold">
                            {{ completedTasks }} dari {{ totalTasks }} Tugas Selesai ({{ progressPercentage }}%)
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span v-if="overdueTasks > 0" class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-semibold flex items-center gap-1">
                            <AlertCircle class="w-3.5 h-3.5" /> {{ overdueTasks }} Tugas Terlewat
                        </span>
                    </div>
                </div>

                <div class="w-full bg-neutral-100 dark:bg-neutral-800 h-2.5 rounded-full overflow-hidden">
                    <div
                        class="bg-emerald-500 h-full transition-all duration-500"
                        :style="{ width: `${progressPercentage}%` }"
                    ></div>
                </div>

                <!-- Filter Tabs -->
                <div class="flex items-center gap-2 pt-2 border-t dark:border-neutral-800 overflow-x-auto scrollbar-none text-xs font-medium">
                    <button
                        @click="selectedFilter = 'all'"
                        :class="['px-3 py-1.5 rounded-lg transition', selectedFilter === 'all' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800']"
                    >
                        Semua ({{ totalTasks }})
                    </button>
                    <button
                        @click="selectedFilter = 'pending'"
                        :class="['px-3 py-1.5 rounded-lg transition', selectedFilter === 'pending' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800']"
                    >
                        Belum Selesai ({{ totalTasks - completedTasks }})
                    </button>
                    <button
                        @click="selectedFilter = 'overdue'"
                        :class="['px-3 py-1.5 rounded-lg transition', selectedFilter === 'overdue' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800']"
                    >
                        Terlewat ({{ overdueTasks }})
                    </button>
                    <button
                        @click="selectedFilter = 'critical'"
                        :class="['px-3 py-1.5 rounded-lg transition', selectedFilter === 'critical' ? 'bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900' : 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800']"
                    >
                        Tugas Krusial
                    </button>
                </div>
            </div>

            <!-- Phases and Tasks -->
            <div class="space-y-6">
                <div
                    v-for="phase in phases"
                    :key="phase.id"
                    class="border rounded-xl bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm overflow-hidden"
                >
                    <!-- Phase Header -->
                    <div class="p-4 bg-neutral-50/50 dark:bg-neutral-800/40 border-b dark:border-neutral-800 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-base text-neutral-900 dark:text-neutral-100">{{ phase.name }}</h3>
                            <span class="text-xs text-neutral-500">
                                {{ phase.tasks.filter(t => t.status === 'completed').length }} / {{ phase.tasks.length }} tugas selesai
                            </span>
                        </div>
                        <button
                            @click="openAddTask(phase.id)"
                            class="text-xs font-semibold px-2.5 py-1 border rounded bg-white dark:bg-neutral-900 dark:border-neutral-700 hover:bg-neutral-50 flex items-center gap-1"
                        >
                            <Plus class="w-3.5 h-3.5" /> Tambah
                        </button>
                    </div>

                    <!-- Tasks List -->
                    <div class="divide-y dark:divide-neutral-800">
                        <div
                            v-for="task in phase.tasks.filter(filterTask)"
                            :key="task.id"
                            :class="[
                                'p-4 flex items-start justify-between gap-3 hover:bg-neutral-50/50 dark:hover:bg-neutral-800/20 transition',
                                isOverdue(task) ? 'bg-rose-50/30 dark:bg-rose-950/10' : ''
                            ]"
                        >
                            <div class="flex items-start gap-3 flex-1">
                                <!-- Status Toggle Button -->
                                <button
                                    @click="toggleStatus(task)"
                                    class="mt-0.5 text-neutral-400 hover:text-neutral-600 transition"
                                >
                                    <CheckCircle v-if="task.status === 'completed'" class="w-5 h-5 text-emerald-500" />
                                    <Clock v-else-if="task.status === 'in_progress'" class="w-5 h-5 text-amber-500" />
                                    <Circle v-else class="w-5 h-5" />
                                </button>

                                <div class="space-y-1 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            :class="[
                                                'font-medium text-sm',
                                                task.status === 'completed' ? 'line-through text-neutral-400 dark:text-neutral-500' : 'text-neutral-900 dark:text-neutral-100'
                                            ]"
                                        >
                                            {{ task.title }}
                                        </span>

                                        <span
                                            v-if="task.is_critical"
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300"
                                        >
                                            KRUSIAL
                                        </span>

                                        <span
                                            v-if="isOverdue(task)"
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300"
                                        >
                                            TERLEWAT
                                        </span>
                                    </div>

                                    <p v-if="task.description" class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ task.description }}
                                    </p>

                                    <div v-if="task.due_date" class="text-[11px] text-neutral-400 flex items-center gap-1">
                                        <span>Batas: {{ task.due_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-1">
                                <button
                                    @click="openEditTask(task)"
                                    class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded"
                                >
                                    <Edit2 class="w-4 h-4" />
                                </button>
                                <button
                                    @click="deleteTask(task)"
                                    class="p-1 text-neutral-400 hover:text-rose-600 rounded"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="phase.tasks.filter(filterTask).length === 0"
                            class="p-6 text-center text-xs text-neutral-400 italic"
                        >
                            Tidak ada tugas pada filter ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Tugas -->
        <div v-if="isTaskModalOpen" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-neutral-900 border dark:border-neutral-800 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-lg">
                        {{ editingTask ? 'Edit Tugas' : 'Tambah Tugas Baru' }}
                    </h3>
                    <button @click="isTaskModalOpen = false" class="text-neutral-400 hover:text-neutral-600">✕</button>
                </div>

                <form @submit.prevent="submitTask" class="space-y-4 text-sm">
                    <div>
                        <label class="block font-medium mb-1">Fase Roadmap</label>
                        <select
                            v-model="taskForm.timeline_phase_id"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        >
                            <option v-for="p in phases" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Judul Tugas</label>
                        <input
                            v-model="taskForm.title"
                            type="text"
                            required
                            placeholder="Contoh: Booking MUA Pengantin"
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        />
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Keterangan / Checklist Detail</label>
                        <textarea
                            v-model="taskForm.description"
                            rows="2"
                            placeholder="Catatan pendukung atau detail tugas..."
                            class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1">Batas Waktu (Deadline)</label>
                            <input
                                v-model="taskForm.due_date"
                                type="date"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            />
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Status Progres</label>
                            <select
                                v-model="taskForm.status"
                                class="w-full px-3 py-2 border rounded-lg bg-transparent dark:border-neutral-800"
                            >
                                <option value="todo">Belum Mulai</option>
                                <option value="in_progress">Sedang Proses</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            type="checkbox"
                            id="is_critical"
                            v-model="taskForm.is_critical"
                            class="rounded dark:border-neutral-800"
                        />
                        <label for="is_critical" class="font-medium text-xs">Tandai sebagai Tugas Krusial / Kritis</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="isTaskModalOpen = false"
                            class="px-4 py-2 border rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="taskForm.processing"
                            class="px-4 py-2 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 font-medium rounded-lg hover:opacity-90 disabled:opacity-50"
                        >
                            {{ editingTask ? 'Simpan Perubahan' : 'Tambah Tugas' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
