<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/OnboardingController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

const page = usePage();

const form = useForm({
    partner_one_name: '',
    partner_two_name: '',
    target_date: '',
    budget_total: '',
    guest_count: '',
    city: '',
    religion: '',
    tradition: '',
});

function submit(): void {
    const teamSlug = page.props.currentTeam?.slug ?? '';
    form.submit(store({ current_team: teamSlug }));
}
</script>

<template>
    <Head title="Mulai rencanakan pernikahan" />

    <main class="min-h-screen bg-stone-50 px-4 py-10 dark:bg-stone-950">
        <div class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-sm sm:p-10 dark:bg-stone-900">
            <p class="text-sm font-medium text-rose-600">Nol ke Nikah</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900 dark:text-white">Mari mulai dari cerita kalian</h1>
            <p class="mt-3 text-stone-600 dark:text-stone-300">Kami akan membuat roadmap persiapan yang menyesuaikan rencana pernikahan kalian.</p>

            <form class="mt-8 grid gap-5 sm:grid-cols-2" @submit.prevent="submit">
                <div>
                    <Label for="partner_one_name">Nama kamu</Label>
                    <Input id="partner_one_name" v-model="form.partner_one_name" class="mt-2" autocomplete="name" />
                    <InputError :message="form.errors.partner_one_name" />
                </div>
                <div>
                    <Label for="partner_two_name">Nama pasangan</Label>
                    <Input id="partner_two_name" v-model="form.partner_two_name" class="mt-2" />
                    <InputError :message="form.errors.partner_two_name" />
                </div>
                <div>
                    <Label for="target_date">Target tanggal nikah</Label>
                    <Input id="target_date" v-model="form.target_date" class="mt-2" type="date" />
                    <p class="mt-1 text-xs text-stone-500">Kosongkan jika belum menentukan tanggal.</p>
                    <InputError :message="form.errors.target_date" />
                </div>
                <div>
                    <Label for="budget_total">Estimasi budget (Rp)</Label>
                    <Input id="budget_total" v-model="form.budget_total" class="mt-2" inputmode="numeric" type="number" min="0" />
                    <InputError :message="form.errors.budget_total" />
                </div>
                <div>
                    <Label for="guest_count">Estimasi jumlah tamu</Label>
                    <Input id="guest_count" v-model="form.guest_count" class="mt-2" inputmode="numeric" type="number" min="1" />
                    <InputError :message="form.errors.guest_count" />
                </div>
                <div>
                    <Label for="city">Kota acara</Label>
                    <Input id="city" v-model="form.city" class="mt-2" />
                    <InputError :message="form.errors.city" />
                </div>
                <div>
                    <Label for="religion">Agama</Label>
                    <Input id="religion" v-model="form.religion" class="mt-2" placeholder="Opsional" />
                </div>
                <div>
                    <Label for="tradition">Adat</Label>
                    <Input id="tradition" v-model="form.tradition" class="mt-2" placeholder="Opsional" />
                </div>
                <div class="sm:col-span-2">
                    <Button class="w-full" :disabled="form.processing" type="submit">{{ form.processing ? 'Membuat roadmap…' : 'Buat roadmap saya' }}</Button>
                </div>
            </form>
        </div>
    </main>
</template>
