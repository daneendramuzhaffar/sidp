{{-- filepath: resources/views/workers/create.blade.php --}}
<x-layouts.app :title="__('Tambah Perkerjaan Baru')">
    <form method="POST" action="{{ route('WorkTypes.store') }}" class="max-w-md mx-auto mt-8">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama Pekerjaan</label>
            <input type="text" name="nama_pekerjaan" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Estimasi Pengerjaan</label>
            <input
                type="number"
                name="flatrate"
                min="15"
                step="15"
                value="15"
                class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"/>
            <span class="text-xs text-gray-500 dark:text-gray-400">Kelipatan 15 menit</span>
        </div>
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-layouts.app>