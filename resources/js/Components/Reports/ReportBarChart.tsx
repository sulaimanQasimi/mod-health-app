import * as echarts from 'echarts';
import { useEffect, useRef } from 'react';

interface ReportBarChartProps {
    labels: string[];
    values: number[];
    color?: string;
    height?: number;
    horizontal?: boolean;
}

const DEFAULT_COLOR = '#06b6d4';

export default function ReportBarChart({
    labels,
    values,
    color = DEFAULT_COLOR,
    height = 280,
    horizontal = true,
}: ReportBarChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current || !labels.length) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const total = values.reduce((sum, value) => sum + value, 0);

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: (params: { dataIndex: number; value: number; name: string }[]) => {
                    const item = params[0];
                    const pct = total ? Math.round((item.value / total) * 100) : 0;
                    return `${item.name}<br/>${item.value} (${pct}%)`;
                },
            },
            grid: horizontal
                ? { left: 110, right: 24, top: 16, bottom: 24 }
                : { left: 40, right: 16, top: 24, bottom: 48 },
            xAxis: horizontal
                ? { type: 'value', minInterval: 1, axisLabel: { color: textColor } }
                : {
                      type: 'category',
                      data: labels,
                      axisLabel: { color: textColor, rotate: labels.length > 8 ? 30 : 0 },
                  },
            yAxis: horizontal
                ? {
                      type: 'category',
                      data: labels,
                      axisLabel: { color: textColor, width: 96, overflow: 'truncate' },
                  }
                : { type: 'value', minInterval: 1, axisLabel: { color: textColor } },
            series: [
                {
                    type: 'bar',
                    data: values,
                    barMaxWidth: 28,
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(horizontal ? 1 : 0, 0, 0, horizontal ? 0 : 1, [
                            { offset: 0, color },
                            { offset: 1, color: `${color}99` },
                        ]),
                        borderRadius: horizontal ? [0, 6, 6, 0] : [6, 6, 0, 0],
                    },
                },
            ],
        });

        const handleResize = () => chart.resize();
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            chart.dispose();
        };
    }, [color, height, horizontal, labels, values]);

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
