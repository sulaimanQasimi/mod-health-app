import * as echarts from 'echarts';
import 'echarts-wordcloud';
import { useEffect, useRef } from 'react';
import { WordCloudItem } from '../../types/dashboard';

interface WordCloudChartProps {
    data: WordCloudItem[];
    height?: number;
}

export default function WordCloudChart({ data, height = 360 }: WordCloudChartProps) {
    const chartRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!chartRef.current || !data.length) {
            return;
        }

        const chart = echarts.init(chartRef.current);
        chart.setOption({
            tooltip: {
                show: true,
                formatter: (params: { name: string; value: number }) =>
                    `<b>${params.name}</b><br/>${params.value}`,
            },
            series: [
                {
                    type: 'wordCloud',
                    shape: 'circle',
                    gridSize: 8,
                    sizeRange: [14, 56],
                    rotationRange: [-45, 45],
                    textStyle: {
                        fontFamily: 'Inter, sans-serif',
                        fontWeight: 'bold',
                    },
                    data: data.map((item) => ({
                        name: item.name,
                        value: item.weight,
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
    }, [data]);

    if (!data.length) {
        return <div className="flex h-40 items-center justify-center text-sm text-gray-500">—</div>;
    }

    return <div ref={chartRef} style={{ height }} />;
}
