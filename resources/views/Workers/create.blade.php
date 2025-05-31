{{-- filepath: resources/views/workers/create.blade.php --}}
<x-layouts.app :title="__('Tambah Teknisi')">
    <form method="POST" action="{{ route('workers.store') }}" class="max-w-md mx-auto mt-8">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama</label>
            <input type="text" name="nama" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Status</label>
            <select name="status" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                <option value="aktif">Aktif</option>
                <option value="training">Training</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Mulai</label>
            <select name="mulai" class="border border-neutral-200 dark:border-neutral-700 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                <option value="08:00:00">08:00 - 16.00</option>
                <option value="09:00:00">09:00 - 17.00</option>
            </select>
        </div>
        <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</x-layouts.app>