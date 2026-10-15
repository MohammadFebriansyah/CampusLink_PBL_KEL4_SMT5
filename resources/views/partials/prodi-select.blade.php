{{--
    Pilihan program studi, dikelompokkan per jurusan dari config/kampus.php.
    Jurusan tidak ditanyakan: diturunkan dari program studi di server.
    Parameter: nilai (isi awal)
--}}
<div>
    <label for="program_studi" class="mb-2 block text-sm font-medium">Program studi</label>
    <select id="program_studi" name="program_studi" required
            @error('program_studi') aria-invalid="true" aria-describedby="program_studi-galat" @enderror
            class="field @error('program_studi') border-ember @enderror">
        <option value="" disabled @selected(empty(old('program_studi', $nilai ?? null)))>Pilih program studi</option>
        @foreach (config('kampus.jurusan') as $jurusan => $daftar)
            <optgroup label="{{ $jurusan }}">
                @foreach ($daftar as $prodi)
                    <option value="{{ $prodi }}" @selected(old('program_studi', $nilai ?? null) === $prodi)>{{ $prodi }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <p class="mt-1.5 text-caption text-graphite">Jurusan terisi otomatis sesuai program studi.</p>

    @error('program_studi')
        <p id="program_studi-galat" class="mt-1.5 flex items-start gap-1.5 text-sm">
            <svg class="mt-0.5 size-4 shrink-0 text-ember" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v7h-2V7Zm0 9h2v2h-2v-2Z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
