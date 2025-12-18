@props(['tenants'])

@role('superadministrator')
    <div class="mb-1 p-3 bg-gray-50 border border-gray-200 rounded-lg flex flex-col sm:flex-row items-start sm:items-center gap-3">

        <div class="flex items-center gap-2 text-gray-700 font-bold text-sm whitespace-nowrap">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter Kampus:
        </div>

        <select {{ $attributes }} class="select select-bordered select-sm w-full text-xs sm:max-w-xs">
            <option value="">-- Tampilkan Semua Data (Global) --</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->tenant_id }}">{{ $tenant->name }}</option>
            @endforeach
        </select>

    </div>
@endrole
