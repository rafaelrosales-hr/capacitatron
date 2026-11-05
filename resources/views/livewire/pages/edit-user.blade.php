<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Ocupation;

new #[Layout('layouts.app')] class extends Component {
    public User $user;

    public string $name = '';
    public string $surname = '';
    public string $second_surname = '';
    public string $curp = '';
    public ?string $email = '';
    public string $state = '';
    public string $municipality = '';
    public ?string $ocupation = '';
    public string $education_level = '';
    public string $major_title = '';
    public string $institution = '';
    public ?string $workplace = '';
    public ?string $area = '';
    public ?string $major = '';
    public ?string $major_description = '';
    public $ocupation_key = null;
    public ?string $password = '';
    public bool $is_active = true;

    public function mount(User $user): void
    {
        $this->user = $user;

        $this->name = $user->name;
        $this->surname = $user->surname;
        $this->second_surname = $user->second_surname;
        $this->curp = $user->curp;
        $this->email = $user->email;
        $this->state = $user->state;
        $this->municipality = $user->municipality;
        $this->ocupation = $user->ocupation;
        $this->education_level = $user->education_level;
        $this->major_title = $user->major_title;
        $this->institution = $user->institution;
        $this->workplace = $user->workplace;
        $this->area = $user->area;
        $this->major = $user->major;
        $this->major_description = $user->major_description;
        $this->ocupation_key = $user->ocupation_key;
        $this->is_active = (bool) $user->is_active;
    }

    public function save(): void
    {
        $this->curp = Str::upper(trim($this->curp));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:30'],
            'surname' => ['required', 'string', 'max:30'],
            'second_surname' => ['required', 'string', 'max:30'],
            'curp' => ['required', 'string', 'size:18', Rule::unique('users', 'curp')->ignore($this->user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'state' => ['required', 'string', 'max:30'],
            'municipality' => ['required', 'string', 'max:50'],
            'ocupation' => ['nullable', 'string', 'max:255'],
            'education_level' => ['required', 'string', 'max:50'],
            'major_title' => ['required', 'string', 'max:255'],
            'institution' => ['required', Rule::in(['PÚBLICA', 'PRIVADA'])],
            'workplace' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:30'],
            'major' => ['nullable', 'string', 'max:255'],
            'major_description' => ['nullable', 'string', 'max:255'],
            'ocupation_key' => ['nullable', 'integer', 'exists:ocupations,key'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['boolean'],
        ]);

        // Cadenas vacías -> null (evita choques con índices unique y datos basura)
        $validated = array_map(fn($v) => $v === '' ? null : $v, $validated);

        // Solo actualizar la contraseña si se escribió una nueva
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $this->user->update($validated);

        $this->dispatch(
            'toast',
            title: 'Usuario actualizado',
            message: 'Los cambios se guardaron exitosamente.'
        );

        $this->redirect(route('users-manager'), navigate: true);
    }

    public function with(): array
    {
        return [
            'ocupations' => Ocupation::all(),
        ];
    }
}; ?>

<div class="w-full mx-auto space-y-6">
    <!-- Encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-text font-syne">Editar Usuario</h1>
            <p class="text-sm text-text-muted">Modifica los datos del usuario seleccionado.</p>
        </div>
        <a
            href="{{ route('users-manager') }}"
            wire:navigate
            class="px-4 py-2 text-sm font-medium text-text-muted bg-surface-hover/40 border border-border rounded-xl hover:bg-surface-hover transition-colors">
            Volver
        </a>
    </div>

    @php
    $input = 'w-full px-4 py-2 text-sm bg-background/50 border border-border rounded-xl text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all';
    $label = 'block text-xs font-semibold uppercase text-text-muted mb-2';
    $error = 'text-xs text-rose-500 mt-1 block';
    @endphp

    <!-- Formulario -->
    <form wire:submit="save" class="space-y-8 rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-8">

        <!-- Datos personales -->
        <section class="space-y-6">
            <h2 class="text-sm font-bold text-text font-syne">Datos personales</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="{{ $label }}">Nombre</label>
                    <input type="text" wire:model="name" maxlength="30" class="{{ $input }}">
                    @error('name') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Apellido Paterno</label>
                    <input type="text" wire:model="surname" maxlength="30" class="{{ $input }}">
                    @error('surname') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Apellido Materno</label>
                    <input type="text" wire:model="second_surname" maxlength="30" class="{{ $input }}">
                    @error('second_surname') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $label }}">CURP</label>
                    <input type="text" wire:model="curp" maxlength="18" class="{{ $input }} uppercase">
                    @error('curp') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Correo Electrónico</label>
                    <input type="email" wire:model="email" class="{{ $input }}">
                    @error('email') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $label }}">Estado</label>
                    <input type="text" wire:model="state" maxlength="30" class="{{ $input }}">
                    @error('state') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Municipio</label>
                    <input type="text" wire:model="municipality" maxlength="50" class="{{ $input }}">
                    @error('municipality') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <!-- Formación académica -->
        <section class="space-y-6">
            <h2 class="text-sm font-bold text-text font-syne">Formación académica</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="{{ $label }}">Nivel Educativo</label>
                    <input type="text" wire:model="education_level" maxlength="50" class="{{ $input }}">
                    @error('education_level') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Título / Profesión</label>
                    <input type="text" wire:model="major_title" class="{{ $input }}">
                    @error('major_title') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Institución</label>
                    <select wire:model="institution" class="{{ $input }}">
                        <option value="">Seleccionar...</option>
                        <option value="PÚBLICA">PÚBLICA</option>
                        <option value="PRIVADA">PRIVADA</option>
                    </select>
                    @error('institution') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <!-- Información laboral -->
        <section class="space-y-6">
            <h2 class="text-sm font-bold text-text font-syne">Información laboral</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $label }}">Centro de Trabajo</label>
                    <input type="text" wire:model="workplace" class="{{ $input }}">
                    @error('workplace') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Área</label>
                    <input type="text" wire:model="area" maxlength="30" class="{{ $input }}">
                    @error('area') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $label }}">Puesto</label>
                    <input type="text" wire:model="major" class="{{ $input }}">
                    @error('major') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Puesto en Español</label>
                    <input type="text" wire:model="major_description" class="{{ $input }}">
                    @error('major_description') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="{{ $label }}">Ocupación</label>
                    <input type="text" wire:model="ocupation" class="{{ $input }}">
                    @error('ocupation') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Clave de Ocupación</label>
                    <select wire:model="ocupation_key" class="{{ $input }}">
                        <option value="">Sin ocupación</option>
                        @foreach ($ocupations as $oc)
                        <option value="{{ $oc->key }}">{{ $oc->key }} - {{ $oc->name }}</option>
                        @endforeach
                    </select>
                    @error('ocupation_key') <span class="{{ $error }}">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <!-- Cuenta -->
        <section class="space-y-6 mt-6">         
            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    wire:model="is_active"
                    id="is_active"
                    class="w-4 h-4 rounded border-border bg-background text-accent focus:ring-accent focus:ring-offset-background cursor-pointer">
                <label for="is_active" class="text-sm font-medium text-text-muted cursor-pointer">Usuario Activo</label>
            </div>
        </section>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-4 border-t border-border/60">
            <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-accent rounded-xl hover:opacity-90 active:scale-[0.98] transition-all shadow-md cursor-pointer">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>