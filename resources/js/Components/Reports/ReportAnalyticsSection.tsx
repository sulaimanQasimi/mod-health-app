import { Card } from 'flowbite-react';
import ReportBarChart from './ReportBarChart';
import ReportDonutChart from './ReportDonutChart';
import ReportTrendChart from './ReportTrendChart';
import { ReportChartCard } from './reportTypes';

interface ReportAnalyticsSectionProps {
    title?: string;
    charts: ReportChartCard[];
}

export default function ReportAnalyticsSection({ title, charts }: ReportAnalyticsSectionProps) {
    const visible = charts.filter((chart) => chart.labels.length > 0 && chart.values.some((v) => v > 0));

    if (!visible.length) {
        return null;
    }

    const gridCols =
        visible.length === 1
            ? 'grid-cols-1'
            : visible.length === 2
              ? 'md:grid-cols-2'
              : 'md:grid-cols-2 xl:grid-cols-3';

    return (
        <div className="space-y-3">
            {title ? (
                <h2 className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                    <i className="bx bx-pie-chart-alt-2 text-cyan-500" />
                    {title}
                </h2>
            ) : null}
            <div className={`grid gap-4 ${gridCols}`}>
                {visible.map((chart) => (
                    <Card key={chart.key} className="!shadow-sm">
                        <h3 className="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            {chart.title}
                        </h3>
                        {chart.type === 'donut' ? (
                            <ReportDonutChart
                                labels={chart.labels}
                                values={chart.values}
                                colors={chart.colors}
                            />
                        ) : chart.type === 'trend' ? (
                            <ReportTrendChart
                                labels={chart.labels}
                                values={chart.values}
                                color={chart.color}
                            />
                        ) : (
                            <ReportBarChart
                                labels={chart.labels}
                                values={chart.values}
                                color={chart.color}
                            />
                        )}
                    </Card>
                ))}
            </div>
        </div>
    );
}
