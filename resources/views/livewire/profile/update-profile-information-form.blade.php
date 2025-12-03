<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public string $nip = '';
    public string $nim = '';
    public string $alamat = '';
    public ?string $tanggal_lahir = null;
    
    public string $academic_info = ''; 

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->hasRole('pengajar') && $user->pengajar) {
            $this->nip = $user->pengajar->nip ?? '';
            $this->alamat = $user->pengajar->alamat ?? '';
            $this->tanggal_lahir = $user->pengajar->tanggal_lahir ? $user->pengajar->tanggal_lahir->format('Y-m-d') : null;
            $this->academic_info = $user->pengajar->studyProgram->name ?? '-';
        } 
        elseif ($user->hasRole('siswa') && $user->siswa) {
            $this->nim = $user->siswa->nim ?? '';
            $this->alamat = $user->siswa->alamat ?? '';
            $this->tanggal_lahir = $user->siswa->tanggal_lahir ? $user->siswa->tanggal_lahir->format('Y-m-d') : null;
            $this->academic_info = $user->siswa->studyProgram->name ?? '-';
        } 
        elseif ($user->hasRole('staff_prodi') && $user->staffProdi) {
            $this->nip = $user->staffProdi->nip ?? '';
            $this->academic_info = $user->staffProdi->studyProgram->name ?? '-';
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);
        if ($user->hasRole('pengajar')) {
            $this->validate([
                'nip' => ['required', 'string', Rule::unique('pengajars')->ignore($user->pengajar->id ?? null)],
                'alamat' => ['nullable', 'string'],
                'tanggal_lahir' => ['nullable', 'date'],
            ]);

            $user->pengajar()->update([
                'nip' => $this->nip,
                'alamat' => $this->alamat,
                'tanggal_lahir' => $this->tanggal_lahir,
            ]);
        } 
        elseif ($user->hasRole('siswa')) {
            $this->validate([
                'nim' => ['required', 'string', Rule::unique('siswas')->ignore($user->siswa->id ?? null)],
                'alamat' => ['nullable', 'string'],
                'tanggal_lahir' => ['nullable', 'date'],
            ]);

            $user->siswa()->update([
                'nim' => $this->nim,
                'alamat' => $this->alamat,
                'tanggal_lahir' => $this->tanggal_lahir,
            ]);
        }
        elseif ($user->hasRole('staff_prodi')) {
            $this->validate([
                'nip' => ['required', 'string', Rule::unique('staff_prodis')->ignore($user->staffProdi->id ?? null)],
            ]);
            
            $user->staffProdi()->update([
                'nip' => $this->nip,
            ]);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if(auth()->user()->hasRole(['pengajar', 'siswa', 'staff_prodi']))
            <div class="p-4 bg-gray-50 border rounded-lg space-y-4">
                <h3 class="font-bold text-gray-700">Data Akademik</h3>

                <div>
                    <x-input-label :value="__('Unit Akademik (Prodi/Jurusan)')" />
                    <x-text-input :value="$academic_info" type="text" class="mt-1 block w-full bg-gray-200 text-gray-500 cursor-not-allowed" disabled />
                    <p class="text-xs text-gray-500 mt-1">Hubungi admin jika ingin mengubah unit akademik.</p>
                </div>

                @if(auth()->user()->hasRole(['pengajar', 'staff_prodi']))
                    <div>
                        <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai)')" />
                        
                        <x-text-input wire:model="nip" id="nip" type="text" class="mt-1 block w-full" 
                                      @disabled(!auth()->user()->hasRole(['superadministrator', 'admin'])) />
                        
                        <x-input-error class="mt-2" :messages="$errors->get('nip')" />
                        
                        @if(!auth()->user()->hasRole(['superadministrator', 'admin']))
                            <p class="text-xs text-gray-500 mt-1">Hubungi admin untuk memperbarui NIP.</p>
                        @endif
                    </div>
                @endif

                @role('siswa')
                    <div>
                        <x-input-label for="nim" :value="__('NIM (Nomor Induk Mahasiswa)')" />
                        
                        <x-text-input wire:model="nim" id="nim" type="text" class="mt-1 block w-full" 
                                      @disabled(!auth()->user()->hasRole(['superadministrator', 'admin'])) />
                        
                        <x-input-error class="mt-2" :messages="$errors->get('nim')" />

                        @if(!auth()->user()->hasRole(['superadministrator', 'admin']))
                            <p class="text-xs text-gray-500 mt-1">Hubungi admin untuk memperbarui NIM.</p>
                        @endif
                    </div>
                @endrole

                @if(auth()->user()->hasRole(['pengajar', 'siswa']))
                    <div>
                        <x-input-label for="alamat" :value="__('Alamat Domisili')" />
                        <textarea wire:model="alamat" id="alamat" class="textarea textarea-bordered w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
                    </div>

                    <div>
                        <x-input-label for="tanggal_lahir" :value="__('Tanggal Lahir')" />
                        <x-text-input wire:model="tanggal_lahir" id="tanggal_lahir" type="date" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('tanggal_lahir')" />
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>