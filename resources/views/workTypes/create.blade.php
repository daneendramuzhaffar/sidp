{{-- filepath: c:\File_Kuliah\PROJECT\sidp\resources\views\WorkTypes\create.blade.php --}}
<x-layouts.app :title="__('Daftar Jenis Pekerjaan')">
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Daftar Jenis Pekerjaan</h1>
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        {{-- Form Tambah --}}
        <form method="POST" action="{{ route('WorkTypes.store') }}" class="mb-8 bg-white dark:bg-neutral-900 rounded-xl shadow p-6 border border-neutral-200 dark:border-neutral-700">
            @csrf
            <div class="flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama Pekerjaan</label>
                    <input type="text" name="nama_pekerjaan" class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" required>
                </div>
                <div class="my-4">
                    <select name="flatrate" class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" required>
                        <option value="15">15 menit</option>
                        <option value="25">25 menit</option>
                        <option value="30">30 menit</option>
                        <option value="40">40 menit</option>
                        <option value="45">45 menit</option>
                        <option value="60">60 menit</option>
                        <!-- dst -->
                    </select>                
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold shadow">Tambah</button>
            </div>
            @error('nama_pekerjaan') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
            @error('flatrate') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
        </form>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">No</th>
                        <th class="px-4 py-2 border-b">Nama Pekerjaan</th>
                        <th class="px-4 py-2 border-b">Estimasi (menit)</th>
                        <th class="px-4 py-2 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workTypes as $i => $workType)
                        <tr class="hover:bg-neutral-100 dark:hover:bg-neutral-800">
                            <td class="px-4 py-2 border-b">{{ $i+1 }}</td>
                            <td class="px-4 py-2 border-b">{{ $workType->nama_pekerjaan }}</td>
                            <td class="px-4 py-2 border-b">{{ $workType->flatrate }}</td>
                            <td class="px-4 py-2 border-b flex gap-2">
                                <button
                                    onclick="openEditModal({{ $workType->id }}, '{{ addslashes($workType->nama_pekerjaan) }}', {{ $workType->flatrate }})"
                                    class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">Edit</button>
                                <button
                                    onclick="openDeleteModal({{ $workType->id }}, '{{ addslashes($workType->nama_pekerjaan) }}')"
                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all hidden">
        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4 relative">
            <button onclick="closeEditModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white text-2xl">&times;</button>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <h2 class="text-xl font-bold mb-4 text-center text-gray-900 dark:text-gray-100">Edit Jenis Pekerjaan</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Nama Pekerjaan</label>
                    <input type="text" id="edit_nama_pekerjaan" name="nama_pekerjaan" class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Estimasi (menit)</label>
                    <input type="number" id="edit_flatrate" name="flatrate" min="15" step="15" class="border border-neutral-300 dark:border-neutral-600 rounded w-full px-3 py-2 bg-white dark:bg-neutral-800 text-gray-900 dark:text-gray-100" required>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Kelipatan 15 menit</span>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold shadow">Simpan</button>
            </form>
        </div>
    </div>

    {{-- Modal Delete --}}
    <div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/70 backdrop-blur-sm transition-all hidden">
        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl shadow-2xl w-full max-w-md mx-4 relative">
            <button onclick="closeDeleteModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white text-2xl">&times;</button>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <h2 class="text-xl font-bold mb-4 text-center text-gray-900 dark:text-gray-100">Hapus Jenis Pekerjaan</h2>
                <p class="mb-4 text-center text-gray-700 dark:text-gray-200">Yakin ingin menghapus <span id="delete_nama_pekerjaan" class="font-semibold"></span>?</p>
                <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold shadow">Hapus</button>
            </form>
        </div>
    </div>

    {{-- Modal Script --}}
    <script>
        function openEditModal(id, nama, flatrate) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('edit_nama_pekerjaan').value = nama;
            document.getElementById('edit_flatrate').value = flatrate;
            document.getElementById('editForm').action = '/WorkTypes/' + id;
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        function openDeleteModal(id, nama) {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('delete_nama_pekerjaan').textContent = nama;
            document.getElementById('deleteForm').action = '/WorkTypes/' + id;
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        // Optional: close modal on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") {
                closeEditModal();
                closeDeleteModal();
            }
        });
    </script>
</x-layouts.app>