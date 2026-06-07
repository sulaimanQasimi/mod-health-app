import * as echarts from 'echarts';
import { useEffect, useRef } from 'react';
import { ChartSeries } from '../../types/dashboard';

interface HorizontalBarChartProps {
    data: ChartSeries;
    label: string;
    color?: string;
    height?: number;
}

export default function HorizontalBarChart({
    data,
    label,
    color = '#0dcaf0',
    height = 280,
}: HorizontalBarChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current || !data.labels.length) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        const total = data.data.reduce((sum, value) => sum + value, 0);

        chart.setOption({
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: (params: { dataIndex: number; value: number }[]) => {
                    const item = params[0];
                    const pct = total ? Math.round((item.value / total) * 100) : 0;
                    return `${data.labels[item.dataIndex]}<br/>${label}: ${item.value}<br/>${pct}%`;
                },
            },
            grid: { left: 120, right: 20, top: 20, bottom: 20 },
            xAxis: { type: 'value', minInterval: 1 },
            yAxis: {
                type: 'category',
                data: data.labels,
                axisLabel: { width: 100, overflow: 'truncate' },
            },
            series: [
                {
                    name: label,
                    type: 'bar',
                    data: data.data,
                    itemStyle: { color },
                },
            ],
        });

        const handleResize = () => chart.resize();
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            chart.dispose();
        };
    }, [color, data, label]);

    if (!data.labels.length) {
        return <div className="flex h-40 items-center justify-center text-sm text-gray-500">—</div>;
    }

    return <div ref={chartRef} style={{ height }} />;
}
