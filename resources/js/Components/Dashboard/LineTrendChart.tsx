import * as echarts from 'echarts';
import { useEffect, useRef } from 'react';
import { ChartSeries } from '../../types/dashboard';

interface LineTrendChartProps {
    data: ChartSeries;
    color?: string;
    height?: number;
}

export default function LineTrendChart({ data, color = '#6964ff', height = 320 }: LineTrendChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        chart.setOption({
            tooltip: { trigger: 'axis' },
            grid: { left: 40, right: 20, top: 20, bottom: 40 },
            xAxis: {
                type: 'category',
                data: data.labels,
                boundaryGap: false,
            },
            yAxis: {
                type: 'value',
                minInterval: 1,
            },
            series: [
                {
                    type: 'line',
                    smooth: true,
                    data: data.data,
                    areaStyle: { color: `${color}22` },
                    lineStyle: { color, width: 3 },
                    itemStyle: { color },
                    showSymbol: true,
                },
            ],
        });

        const handleResize = () => chart.resize();
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('resize', handleResize);
            chart.dispose();
        };
    }, [color, data]);

    return <div ref={chartRef} style={{ height }} />;
}
