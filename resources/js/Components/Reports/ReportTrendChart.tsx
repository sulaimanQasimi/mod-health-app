import * as echarts from 'echarts';
import { useEffect, useRef } from 'react';

interface ReportTrendChartProps {
    labels: string[];
    values: number[];
    color?: string;
    height?: number;
}

const DEFAULT_COLOR = '#6366f1';

export default function ReportTrendChart({
    labels,
    values,
    color = DEFAULT_COLOR,
    height = 280,
}: ReportTrendChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current || !labels.length) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';

        chart.setOption({
            tooltip: { trigger: 'axis' },
            grid: { left: 40, right: 16, top: 24, bottom: 40 },
            xAxis: {
                type: 'category',
                data: labels,
                boundaryGap: false,
                axisLabel: { color: textColor, rotate: labels.length > 10 ? 30 : 0 },
            },
            yAxis: {
                type: 'value',
                minInterval: 1,
                axisLabel: { color: textColor },
                splitLine: { lineStyle: { color: isDark ? '#374151' : '#e5e7eb' } },
            },
            series: [
                {
                    type: 'line',
                    smooth: true,
                    data: values,
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: `${color}55` },
                            { offset: 1, color: `${color}08` },
                        ]),
                    },
                    lineStyle: { color, width: 3 },
                    itemStyle: { color },
                    showSymbol: labels.length <= 20,
                },
            ],
        });

        const handleResize = () => chart.resize();
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            chart.dispose();
        };
    }, [color, height, labels, values]);

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
