import * as echarts from 'echarts';
import { useEffect, useRef } from 'react';

interface ReportDonutChartProps {
    labels: string[];
    values: number[];
    colors?: string[];
    height?: number;
}

const DEFAULT_COLORS = ['#10b981', '#f59e0b', '#06b6d4', '#8b5cf6', '#f43f5e', '#3b82f6', '#14b8a6'];

export default function ReportDonutChart({
    labels,
    values,
    colors = DEFAULT_COLORS,
    height = 280,
}: ReportDonutChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current || !labels.length) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#d1d5db' : '#374151';

        chart.setOption({
            tooltip: {
                trigger: 'item',
                formatter: '{b}: {c} ({d}%)',
            },
            legend: {
                bottom: 0,
                textStyle: { color: textColor },
            },
            series: [
                {
                    type: 'pie',
                    radius: ['48%', '72%'],
                    center: ['50%', '44%'],
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: isDark ? '#1f2937' : '#fff',
                        borderWidth: 2,
                    },
                    label: { show: false },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 13,
                            fontWeight: 600,
                            color: textColor,
                        },
                    },
                    data: labels.map((name, index) => ({
                        name,
                        value: values[index] ?? 0,
                        itemStyle: { color: colors[index % colors.length] },
                    })),
                },
            ],
        });

        const handleResize = () => chart.resize();
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            chart.dispose();
        };
    }, [colors, height, labels, values]);

    if (!labels.length) {
        return (
            <div
                className="flex items-center justify-center text-sm text-gray-400 dark:text-gray-500"
                style={{ height }}
            >
                —
            </div>
        );
    }

    return <div ref={chartRef} style={{ height }} />;
}
