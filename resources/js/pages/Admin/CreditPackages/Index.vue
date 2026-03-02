<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface CreditPackage {
  id: number;
  name: string;
  description: string | null;
  credits: number;
  price: number;
  formatted_price: string;
  is_active: boolean;
  sort_order: number;
  deleted_at: string | null;
  created_at: string;
}

const props = defineProps<{ packages: CreditPackage[] }>();

const confirmingDelete = ref<number | null>(null);

const toggleActive = (pkg: CreditPackage) => {
  router.post(`/admin/credit-packages/${pkg.id}/toggle-active`, {}, {
    preserveScroll: true,
  });
};

const destroy = (pkg: CreditPackage) => {
  confirmingDelete.value = pkg.id;
};

const confirmDelete = () => {
  if (!confirmingDelete.value) return;
  router.delete(`/admin/credit-packages/${confirmingDelete.value}`, {
    preserveScroll: true,
    onFinish: () => { confirmingDelete.value = null; },
  });
};

const restore = (id: number) => {
  router.post(`/admin/credit-packages/${id}/restore`, {}, { preserveScroll: true });
};

const formatNumber = (num: number) => new Intl.NumberFormat('id-ID').format(num);
</script>

<template>
  <Head title="Paket Kredit - Admin" />

  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Paket Kredit</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Kelola paket topup yang bisa dibeli pengguna</p>
          </div>
          <Link
            href="/admin/credit-packages/create"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
          >
            + Tambah Paket
          </Link>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
          <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Paket</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kredit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Harga</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Urutan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <tr
                v-for="pkg in packages"
                :key="pkg.id"
                :class="pkg.deleted_at ? 'opacity-50 bg-slate-50 dark:bg-slate-900/40' : ''"
              >
                <td class="px-6 py-4">
                  <div class="font-medium text-slate-900 dark:text-white">{{ pkg.name }}</div>
                  <div v-if="pkg.description" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ pkg.description }}</div>
                </td>
                <td class="px-6 py-4 text-slate-900 dark:text-white font-semibold">
                  {{ formatNumber(pkg.credits) }} kredit
                </td>
                <td class="px-6 py-4 text-slate-900 dark:text-white">
                  {{ pkg.formatted_price }}
                </td>
                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                  {{ pkg.sort_order }}
                </td>
                <td class="px-6 py-4">
                  <span
                    v-if="pkg.deleted_at"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400"
                  >Dihapus</span>
                  <button
                    v-else
                    @click="toggleActive(pkg)"
                    :class="pkg.is_active
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                      : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity"
                  >
                    {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                </td>
                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                  <template v-if="pkg.deleted_at">
                    <button
                      @click="restore(pkg.id)"
                      class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >Pulihkan</button>
                  </template>
                  <template v-else>
                    <Link
                      :href="`/admin/credit-packages/${pkg.id}/edit`"
                      class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >Edit</Link>
                    <button
                      @click="destroy(pkg)"
                      class="text-sm text-red-600 dark:text-red-400 hover:underline"
                    >Hapus</button>
                  </template>
                </td>
              </tr>
              <tr v-if="packages.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                  Belum ada paket kredit. <Link href="/admin/credit-packages/create" class="text-blue-600 dark:text-blue-400 hover:underline">Buat sekarang</Link>.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="confirmingDelete"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="confirmingDelete = null"
      >
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Hapus Paket?</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            Paket akan disembunyikan dari pengguna. Riwayat transaksi tetap terjaga.
          </p>
          <div class="flex justify-end gap-3">
            <button
              @click="confirmingDelete = null"
              class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
            >Batal</button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
            >Ya, Hapus</button>
          </div>
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>
