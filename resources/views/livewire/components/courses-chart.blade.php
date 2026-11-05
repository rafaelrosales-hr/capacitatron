<?php

use Livewire\Volt\Component;

new class extends Component {
    public $coursesTaken = 0;
    public $coursesMissing = 0;
    public $chartData = [];
    public $chartId = 'coursesChart';

    public function mount(): void
    {
        $this->chartId = 'coursesChart-' . uniqid();
        $user = auth()->user();
        $this->coursesTaken = $user?->coursesTakenCount() ?? 0;
        $this->coursesMissing = $user?->coursesMissingCount() ?? 0;

        $this->chartData = [
            'labels' => ['Completados', 'Faltantes'],
            'data' => [$this->coursesTaken, $this->coursesMissing],
        ];
    }
}; ?>

<div class="h-full flex flex-col">
    <p class="text-lg text-center font-semibold uppercase tracking-[0.24em] text-text mb-4 font-syne">
        Progreso de Cursos de: {{ Auth::user()->name }}
    </p>

    <div class="flex-1 flex items-center justify-center">
        <div class="relative w-48 h-48 rounded-full bg-background backdrop-blur-sm" wire:ignore>
            <canvas id="{{ $chartId }}"></canvas>

            @if($coursesTaken + $coursesMissing > 0)
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-2xl font-bold text-text">
                        {{ round(($coursesTaken / ($coursesTaken + $coursesMissing)) * 100) }}%
                    </div>
                    <div class="text-xs text-text-muted">Completado</div>
                </div>
            </div>
            @else
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-2xl font-bold text-text">0%</div>
                    <div class="text-xs text-text-muted">Sin cursos</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @script
    <script>
        const el = $wire.$el;
        const canvas = el.querySelector('#{{ $chartId }}');
        let chartInstance = null;

        function getThemeColors() {
            const styles = getComputedStyle(document.documentElement);
            const isDark = document.documentElement.classList.contains('dark');

            return {
                accent: styles.getPropertyValue('--color-accent').trim() || '#f97316',
                textMuted: styles.getPropertyValue('--color-text-muted').trim() || '#6b7280',
                surfaceHover: styles.getPropertyValue('--color-surface-hover').trim() || '#e5e7eb',
                tooltipBg: isDark ? 'rgba(255, 255, 255, 0.9)' : 'rgba(0, 0, 0, 0.85)',
                tooltipText: isDark ? '#0F0A0A' : '#fdfdfd',
            };
        }

        function renderChart() {
            if (!canvas || typeof Chart === 'undefined') return;

            const colors = getThemeColors();

            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: @js($chartData['labels']),
                    datasets: [{
                        data: @js($chartData['data']),
                        backgroundColor: [
                            colors.accent,
                            colors.surfaceHover,
                        ],
                        borderColor: [
                            colors.accent,
                            colors.surfaceHover,
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '70%',
                    backgroundColor: 'transparent',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: colors.tooltipBg,
                            titleColor: colors.tooltipText,
                            bodyColor: colors.tooltipText,
                            padding: 12,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

        renderChart();

        window.addEventListener('theme:changed', renderChart);
    </script>
    @endscript
</div>