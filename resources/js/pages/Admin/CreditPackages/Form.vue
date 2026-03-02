<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface CreditPackage {
  id: number;
  name: string;
  description: string | null;
  credits: number;
  price: number;
  is_active: boolean;
  sort_order: number;
}

const props = defineProps<{ package: CreditPackage | null }>();

const isEditing = computed(() => props.package !== null);

const form = useForm({
  name: props.package?.name ?? '',
  description: props.package?.description ?? '',
  credits: props.package?.credits ?? 100,
  price: props.package?.price ?? 10000,
  is_active: props.package?.is_active ?? true,
  sort_order: props.package?.sort_order ?? 0,
});

const submit = () => {
  if (isEditing.value) {
    form.put(`/admin/credit-packages/${props.package!.id}`, {
      onSuccess: () => { /* redirect handled server-side */ },
    });
  } else {
    form.post('/admin/credit-packages', {
      onSuccess: () => { /* redirect handled server-side */ },
    });
  }
};

const formatIDR = (val: number) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
  <Head :title="isEditing ? 'Edit Paket Kredit - Admin' : 'Tambah Paket Kredit - Admin'" />

  <AdminLayout>
    <div class="py-6">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
          <Link
            href="/admin/credit-packages"
            class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
              {{ isEditing ? 'Edit Paket Kredit' : 'Tambah Paket Kredit' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
              {{ isEditing ? `Mengubah "${package?.name}"` : 'Buat paket topup baru untuk pengguna' }}
            </p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-5">

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Nama Paket <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Misal: Paket Starter"
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-400 dark:border-red-400': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Deskripsi singkat paket (opsional)"
              class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            />
          </div>

          <!-- Credits & Price row -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Jumlah Kredit <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.credits"
                type="number"
                min="1"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-400 dark:border-red-400': form.errors.credits }"
              />
              <p v-if="form.errors.credits" class="mt-1 text-xs text-red-500">{{ form.errors.credits }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Harga (IDR) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.price"
                type="number"
                min="1000"
                step="1000"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-400 dark:border-red-400': form.errors.price }"
              />
              <p v-if="form.errors.price" class="mt-1 text-xs text-red-500">{{ form.errors.price }}</p>
              <p v-if="form.price > 0" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ formatIDR(form.price) }}</p>
            </div>
          </div>

          <!-- Sort order & Active row -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Urutan Tampil</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                min="0"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Angka kecil tampil lebih dulu</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
              <label class="flex items-center gap-3 mt-3 cursor-pointer">
                <input type="checkbox" v-model="form.is_active" class="w-4 h-4 text-blue-600 rounded" />
                <span class="text-sm text-slate-700 dark:text-slate-300">Aktif (ditampilkan ke pengguna)</span>
              </label>
            </div>
          </div>

          <!-- Preview -->
          <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Preview Paket</p>
            <div class="flex items-center justify-between">
              <div>
                <span class="font-semibold text-slate-900 dark:text-white">{{ form.name || 'Nama Paket' }}</span>
                <span class="ml-2 text-sm text-slate-500 dark:text-slate-400">· {{ form.credits }} kredit</span>
              </div>
              <span class="font-bold text-blue-600 dark:text-blue-400">{{ formatIDR(form.price) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <Link
              href="/admin/credit-packages"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
            >Batal</Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 transition-colors"
            >
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat Paket') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
