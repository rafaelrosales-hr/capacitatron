<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div>
    <section
        class="bg-white/10 backdrop-blur-md border border-white/20 shadow-lg px-2 py-2 rounded-xl flex justify-between items-center">
        <div>
            <h2 class="text-white font-bold text-2xl" style="font-family: 'Syne', sans-serif;">Carga de Catalogos</h2>
            <h4 class="text-gray-300/70 font-thin text-xs mt-1">Temporal hasta que se termine de desarrollar el proyecto
                Master 2.0</h4>
        </div>
    </section>

    <section
        class="bg-white/10 backdrop-blur-md border border-white/20 shadow-lg px-6 py-5 rounded-xl flex flex-col w-full mt-4">
        <div class="mb-4">
            <h2 class="text-white font-bold text-xl" style="font-family: 'Syne', sans-serif;">
                Carga de catálogo de Áreas Temáticas
            </h2>
        </div>

        <div class="w-full">
            <label for="dropzone-file"
                class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-xl cursor-pointer bg-white/5 border-white/20 backdrop-blur-md hover:bg-white/10 hover:border-blue-500/50 transition-all duration-300 shadow-lg group">
                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                    <div
                        class="p-3 bg-blue-600/10 rounded-full text-blue-400 group-hover:scale-110 group-hover:bg-blue-600/20 transition-all duration-300 mb-4">
                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                        </svg>
                    </div>

                    <p class="mb-2 text-sm text-gray-200"><span class="font-semibold">Haz clic para cargar</span> o
                        arrastra y suelta</p>
                    <p class="text-xs text-gray-400 mb-1">Archivos permitidos: <span
                            class="text-blue-400 font-medium">CSV, XLS o XLSX</span></p>
                </div>

                <input id="dropzone-file" type="file" class="hidden"
                    accept=".csv, .xls, .xlsx, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
            </label>
        </div>
    </section>

    <section
        class="bg-white/10 backdrop-blur-md border border-white/20 shadow-lg px-6 py-5 rounded-xl flex flex-col w-full mt-4">
        <div class="mb-4">
            <h2 class="text-white font-bold text-xl" style="font-family: 'Syne', sans-serif;">
                Carga de catálogo de Ocupaciones
            </h2>
        </div>

        <div class="w-full">
            <label for="dropzone-file"
                class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-xl cursor-pointer bg-white/5 border-white/20 backdrop-blur-md hover:bg-white/10 hover:border-blue-500/50 transition-all duration-300 shadow-lg group">
                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                    <div
                        class="p-3 bg-blue-600/10 rounded-full text-blue-400 group-hover:scale-110 group-hover:bg-blue-600/20 transition-all duration-300 mb-4">
                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                        </svg>
                    </div>

                    <p class="mb-2 text-sm text-gray-200"><span class="font-semibold">Haz clic para cargar</span> o
                        arrastra y suelta</p>
                    <p class="text-xs text-gray-400 mb-1">Archivos permitidos: <span
                            class="text-blue-400 font-medium">CSV, XLS o XLSX</span></p>
                </div>

                <input id="dropzone-file" type="file" class="hidden"
                    accept=".csv, .xls, .xlsx, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
            </label>
        </div>
    </section>
</div>
