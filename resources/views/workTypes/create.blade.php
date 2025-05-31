{{-- filepath: c:\File_Kuliah\PROJECT\sidp\resources\views\workTypes\create.blade.php --}}
<x-layouts.app :title="__('Tambah Pekerjaan Baru')">
    <div class="flex items-center justify-center min-h-screen py-8">
        <form method="POST" action="{{ route('WorkTypes.store') }}" class="w-full max-w-md bg-white dark:bg-neutral-900 rounded-xl shadow-lg p-8 space-y-6 border border-neutral-200 dark:border-neutral-700">
            @csrf
            <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100 mb-4">Tambah Pekerjaan Baru</h1>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama Pekerjaan</label>
                <input type="text" name="nama_pekerjaan" class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Estimasi Pengerjaan</label>
                <input
                    type="number"
                    name="flatrate"
                    min="15"
                    step="15"
                    value="15"
                    class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"/>
                <span class="text-xs text-gray-500 dark:text-gray-400">Kelipatan 15 menit</span>
            </div>
            <button type="submit" class="w-full mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 transition text-white rounded font-semibold shadow">
                Simpan
            </button>
        </form>
    </div>
</x-layouts.app>