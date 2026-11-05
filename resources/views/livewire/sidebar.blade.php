<?php

use function Livewire\Volt\{state};

?>

<aside
    class="w-56 h-screen border-r border-border bg-background flex flex-col justify-between shrink-0 font-montserrat transition-colors">
    <div class="flex flex-col min-h-0">
        <!-- Logo -->
        <div class="flex items-center gap-2 h-16 border-b border-border px-5 shrink-0">
            <img src="{{ asset('/assets/img/logo.jpg') }}" alt="HR Media" class="w-9 h-9 object-contain dark:hidden">
            <img src="{{ asset('/assets/img/logo_hr_mw.png') }}" alt="HR Media" class="w-9 h-9 object-contain hidden dark:block">
            <span class="font-syne font-semibold text-text text-sm">HR Media</span>
        </div>

        <nav class="mt-6 px-3 overflow-y-auto">
            <!-- Principal -->
            <div class="mb-6">
                <span class="block px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-text-muted">
                    Principal
                </span>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                            {{ request()->routeIs('dashboard') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                            <i class="bi bi-columns-gap w-4 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('my_courses') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                            {{ request()->routeIs('my_courses') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                            <i class="bi bi-book w-4 text-center"></i>
                            <span>Mis cursos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('course-requests') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                            {{ request()->routeIs('course-requests') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                            <i class="bi bi-file-earmark-plus w-4 text-center"></i>
                            <span>Solicitudes de cursos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('calendar.cursos') }}"
                            class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                            {{ request()->routeIs('calendar.cursos') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                            <i class="bi bi-calendar3 w-4 text-center"></i>
                            <span>Calendario de Cursos</span>
                        </a>
                    </li>
                </ul>
            </div>

            @role('RRHH')
                <div class="mb-6">
                    <span class="block px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-text-muted">
                        Recursos Humanos
                    </span>
                    <ul class="space-y-1">
                        @can('cursos_media')
                            <li>
                                <a href="{{ route('cursos.hr') }}"
                                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                    {{ request()->routeIs('cursos.hr') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                    <i class="bi bi-journal-bookmark w-4 text-center"></i>
                                    <span>Cursos</span>
                                </a>
                            </li>
                        @endcan

                        <li>
                            <a href="{{ route('pase_lista') }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                {{ request()->routeIs('pase_lista') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                <i class="bi bi-clipboard-check w-4 text-center"></i>
                                <span>Pase de Lista</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('attendace.report') }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                {{ request()->routeIs('attendace.report') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                <i class="bi bi-clipboard-data w-4 text-center"></i>
                                <span>Reporte de asistencia</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('reporte.usuarios.cursos') }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                {{ request()->routeIs('reporte.usuarios.cursos') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                <i class="bi bi-journal-bookmark w-4 text-center"></i>
                                <span>Reporte cursos</span>
                            </a>
                        </li

                         <li>
                            <a href="{{ route('reply-course') }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                {{ request()->routeIs('reply-course') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                <i class="bi bi-reply-all-fill w-4 text-center"></i>
                                <span>Respuesta a cursos</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('catalogos') }}"
                                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                {{ request()->routeIs('catalogos') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                <i class="bi bi-folder w-4 text-center"></i>
                                <span>Manejo de catálogos</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endrole

            @role(['root','RRHH'])
                <div class="mb-6">
                    <span class="block px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-text-muted">
                        Colaboradores
                    </span>
                    <ul class="space-y-1">
                        @can('carga_usuarios')
                            <li>
                                <a href="{{ route('carga-users') }}"
                                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                    {{ request()->routeIs('carga-users') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                    <i class="bi bi-person-add w-4 text-center"></i>
                                    <span>Carga de Colaboradores</span>
                                </a>
                            </li>
                        @endcan
                        @can('carga_usuarios')
                            <li>
                                <a href="{{ route('users-manager') }}"
                                    class="group flex items-center gap-3 px-3 py-2.5 rounded-lg font-semibold text-sm transition-colors
                                    {{ request()->routeIs('users-manager') ? 'bg-accent/15 text-accent' : 'text-text-muted hover:bg-surface-hover hover:text-text' }}">
                                    <i class="bi bi-person-workspace w-4 text-center"></i>
                                    <span>Gestion de Colaboradores</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>
            @endrole
        </nav>
    </div>

    <!-- Usuario -->
    {{-- <div class="border-t border-border p-4 shrink-0">
        <div class="flex items-center gap-3">
            <div
                class="w-9 h-9 rounded-full bg-accent/15 text-accent flex items-center justify-center text-xs font-semibold shrink-0">
                {{ Auth::user()->initials() }}
            </div>
            <div class="min-w-0">
                <span class="block text-text text-sm font-medium truncate">{{ Auth::user()->name }}</span>
                <span class="block text-text-muted text-xs">HR Media</span>
            </div>
        </div>
    </div> --}}
</aside>
