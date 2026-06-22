import {
    DndContext,
    DragEndEvent,
    DragOverlay,
    DragStartEvent,
    PointerSensor,
    closestCenter,
    useDraggable,
    useDroppable,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Head, router } from '@inertiajs/react';
import { Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, ReactNode, useEffect, useMemo, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import NumberOfPatientsBaseOnDepartment from './CustomHooks/NumberOfPatientsBaseOnDepartment';
import NumberOfPatientsBaseOnPatientMiliteryTypes from './CustomHooks/NumberOfPatientsBaseOnPatientMiliteryTypes';

type ReportWidgetType = 'department' | 'militery-types';

interface BranchOption {
    id: number;
    name: string;
}

interface ReportFilters {
    branch_id: string;
    date_from: string;
    date_to: string;
}

interface PlacedWidget {
    id: string;
    type: ReportWidgetType;
}

interface GeneralReportProps {
    filters: ReportFilters;
    hasSearch: boolean;
    filterOptions: {
        branches: BranchOption[];
    };
    urls: {
        current: string;
    };
}

const EMPTY_FILTERS: ReportFilters = {
    branch_id: '',
    date_from: '',
    date_to: '',
};

const WIDGET_CATALOG: Record<ReportWidgetType, { labelKey: string; icon: string }> = {
    department: {
        labelKey: 'global.department_report',
        icon: 'bx-building',
    },
    'militery-types': {
        labelKey: 'global.militery_types',
        icon: 'bx-user-pin',
    },
};

function createWidget(type: ReportWidgetType): PlacedWidget {
    return {
        id: `${type}-${crypto.randomUUID()}`,
        type,
    };
}

function PaletteItem({
    type,
    label,
    icon,
    onAdd,
}: {
    type: ReportWidgetType;
    label: string;
    icon: string;
    onAdd: (type: ReportWidgetType) => void;
}) {
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id: `palette-${type}`,
        data: { source: 'palette', type },
    });

    const style = transform ? { transform: CSS.Translate.toString(transform) } : undefined;

    return (
        <button
            ref={setNodeRef}
            style={style}
            type="button"
            onClick={() => onAdd(type)}
            className={`flex w-full items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-start text-sm font-medium text-gray-800 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30 ${
                isDragging ? 'opacity-50' : ''
            }`}
            {...listeners}
            {...attributes}
        >
            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white">
                <i className={`bx ${icon} text-lg`} />
            </span>
            <span className="flex-1">{label}</span>
            <i className="bx bx-plus text-lg text-indigo-500" />
        </button>
    );
}

function ReportCanvas({
    children,
    isEmpty,
    emptyLabel,
}: {
    children: ReactNode;
    isEmpty: boolean;
    emptyLabel: string;
}) {
    const { setNodeRef, isOver } = useDroppable({ id: 'report-canvas' });

    return (
        <div
            ref={setNodeRef}
            className={`min-h-[280px] rounded-2xl border-2 border-dashed p-4 transition ${
                isOver
                    ? 'border-indigo-400 bg-indigo-50/70 dark:border-indigo-500 dark:bg-indigo-950/20'
                    : 'border-gray-200 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-900/40'
            }`}
        >
            {isEmpty ? (
                <div className="flex h-full min-h-[240px] flex-col items-center justify-center gap-3 text-center text-gray-500 dark:text-gray-400">
                    <div className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/30">
                        <i className="bx bx-layer-plus text-2xl text-indigo-500" />
                    </div>
                    <p className="max-w-md text-sm">{emptyLabel}</p>
                </div>
            ) : (
                <div className="space-y-4">{children}</div>
            )}
        </div>
    );
}

function SortableWidgetCard({
    widget,
    title,
    onRemove,
    children,
}: {
    widget: PlacedWidget;
    title: string;
    onRemove: (id: string) => void;
    children: ReactNode;
}) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: widget.id,
        data: { source: 'canvas', type: widget.type },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div ref={setNodeRef} style={style}>
            <Card className="!shadow-sm">
                <div className="mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            className="flex h-9 w-9 cursor-grab items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-gray-50 active:cursor-grabbing dark:border-gray-700 dark:hover:bg-gray-800"
                            aria-label="Drag widget"
                            {...listeners}
                            {...attributes}
                        >
                            <i className="bx bx-grid-vertical text-xl" />
                        </button>
                        <h3 className="text-base font-semibold text-gray-900 dark:text-white">{title}</h3>
                    </div>
                    <Button type="button" color="light" size="sm" onClick={() => onRemove(widget.id)}>
                        <i className="bx bx-trash me-1" />
                        Remove
                    </Button>
                </div>
                {children}
            </Card>
        </div>
    );
}

export default function GeneralReport({
    filters: serverFilters,
    hasSearch: serverHasSearch,
    filterOptions,
    urls,
}: GeneralReportProps) {
    const { t } = useTranslation();
    const [filters, setFilters] = useState(serverFilters);
    const [hasSearch, setHasSearch] = useState(serverHasSearch);
    const [processing, setProcessing] = useState(false);
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [widgets, setWidgets] = useState<PlacedWidget[]>([]);
    const [activeDragType, setActiveDragType] = useState<ReportWidgetType | null>(null);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 8 },
        }),
    );

    useEffect(() => {
        setFilters(serverFilters);
        setHasSearch(serverHasSearch);
    }, [serverFilters, serverHasSearch]);

    const appliedFilters = hasSearch ? filters : EMPTY_FILTERS;

    const addWidget = (type: ReportWidgetType) => {
        setWidgets((current) => {
            if (current.some((widget) => widget.type === type)) {
                return current;
            }

            return [...current, createWidget(type)];
        });
    };

    const removeWidget = (id: string) => {
        setWidgets((current) => current.filter((widget) => widget.id !== id));
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        setProcessing(true);

        const params: Record<string, string> = { search: '1' };
        if (filters.branch_id) {
            params.branch_id = filters.branch_id;
        }
        if (filters.date_from) {
            params.date_from = filters.date_from;
        }
        if (filters.date_to) {
            params.date_to = filters.date_to;
        }

        router.get(urls.current, params, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleReset = () => {
        setFilters(EMPTY_FILTERS);
        setProcessing(true);
        router.get(urls.current, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    const handleDragStart = (event: DragStartEvent) => {
        const type = event.active.data.current?.type as ReportWidgetType | undefined;
        setActiveDragType(type ?? null);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;
        setActiveDragType(null);

        if (!over) {
            return;
        }

        const activeData = active.data.current as { source?: string; type?: ReportWidgetType };

        if (activeData?.source === 'palette' && activeData.type) {
            if (over.id === 'report-canvas' || widgets.some((widget) => widget.id === over.id)) {
                addWidget(activeData.type);
            }

            return;
        }

        if (activeData?.source === 'canvas' && active.id !== over.id) {
            const oldIndex = widgets.findIndex((widget) => widget.id === active.id);
            const newIndex = widgets.findIndex((widget) => widget.id === over.id);

            if (oldIndex !== -1 && newIndex !== -1) {
                setWidgets((current) => arrayMove(current, oldIndex, newIndex));
            }
        }
    };

    const renderWidget = (widget: PlacedWidget) => {
        if (!hasSearch) {
            return (
                <div className="rounded-xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    {t('global.apply_filters')}
                </div>
            );
        }

        const filterProps = {
            branch_id: appliedFilters.branch_id,
            date_from: appliedFilters.date_from,
            date_to: appliedFilters.date_to,
        };

        if (widget.type === 'department') {
            return <NumberOfPatientsBaseOnDepartment {...filterProps} />;
        }

        return <NumberOfPatientsBaseOnPatientMiliteryTypes {...filterProps} />;
    };

    const widgetIds = useMemo(() => widgets.map((widget) => widget.id), [widgets]);

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragStart={handleDragStart}
                onDragEnd={handleDragEnd}
            >
                <div className={`mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                    <SettingsPageHeader
                        title={t('global.reports')}
                        subtitle={t('global.general')}
                        icon="bx-bar-chart-alt-2"
                        accent="from-indigo-600 to-violet-700"
                        backLabel={t('global.back')}
                    />

                    <Card className="!shadow-sm">
                        <button
                            type="button"
                            onClick={() => setFiltersOpen((open) => !open)}
                            className="flex w-full items-center justify-between gap-3 border-b border-gray-100 px-1 pb-4 text-start dark:border-gray-700"
                        >
                            <span className="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                <i className="bx bx-filter-alt text-indigo-500" />
                                {t('global.advanced_filters')}
                            </span>
                            <i className={`bx ${filtersOpen ? 'bx-chevron-up' : 'bx-chevron-down'} text-xl text-gray-400`} />
                        </button>

                        {filtersOpen && (
                            <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <Label>{t('global.branch')}</Label>
                                        <SearchableSelect
                                            value={filters.branch_id}
                                            onChange={(value) =>
                                                setFilters((prev) => ({ ...prev, branch_id: value }))
                                            }
                                            options={[
                                                { value: '', label: t('global.all') },
                                                ...filterOptions.branches.map((branch) => ({
                                                    value: String(branch.id),
                                                    label: branch.name,
                                                })),
                                            ]}
                                            placeholder={t('global.select')}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.from')}</Label>
                                        <PersianDateInput
                                            value={filters.date_from}
                                            onChange={(value) =>
                                                setFilters((prev) => ({ ...prev, date_from: value }))
                                            }
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('global.to')}</Label>
                                        <PersianDateInput
                                            value={filters.date_to}
                                            onChange={(value) =>
                                                setFilters((prev) => ({ ...prev, date_to: value }))
                                            }
                                        />
                                    </div>
                                </div>
                                <div className="flex flex-wrap gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                                    <Button type="submit" color="blue" disabled={processing}>
                                        {processing ? (
                                            <>
                                                <Spinner size="sm" className="me-2" />
                                                {t('global.loading')}
                                            </>
                                        ) : (
                                            <>
                                                <i className="bx bx-search me-2" />
                                                {t('global.search')}
                                            </>
                                        )}
                                    </Button>
                                    <Button type="button" color="light" onClick={handleReset} disabled={processing}>
                                        <i className="bx bx-refresh me-2" />
                                        {t('global.reset')}
                                    </Button>
                                </div>
                            </form>
                        )}
                    </Card>

                    <div className="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
                        <Card className="!shadow-sm">
                            <div className="mb-4 border-b border-gray-100 pb-4 dark:border-gray-700">
                                <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                    Report Components
                                </h2>
                                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Click or drag a component into the canvas.
                                </p>
                            </div>
                            <div className="space-y-3">
                                {(Object.keys(WIDGET_CATALOG) as ReportWidgetType[]).map((type) => (
                                    <PaletteItem
                                        key={type}
                                        type={type}
                                        label={t(WIDGET_CATALOG[type].labelKey)}
                                        icon={WIDGET_CATALOG[type].icon}
                                        onAdd={addWidget}
                                    />
                                ))}
                            </div>
                        </Card>

                        <Card className="!shadow-sm">
                            <div className="mb-4 border-b border-gray-100 pb-4 dark:border-gray-700">
                                <h2 className="text-base font-semibold text-gray-900 dark:text-white">Report Canvas</h2>
                                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Drop components here to build your report layout.
                                </p>
                            </div>

                            <ReportCanvas
                                isEmpty={widgets.length === 0}
                                emptyLabel="Add report components from the left panel or drag them into this area."
                            >
                                <SortableContext items={widgetIds} strategy={verticalListSortingStrategy}>
                                    {widgets.map((widget) => (
                                        <SortableWidgetCard
                                            key={widget.id}
                                            widget={widget}
                                            title={t(WIDGET_CATALOG[widget.type].labelKey)}
                                            onRemove={removeWidget}
                                        >
                                            {renderWidget(widget)}
                                        </SortableWidgetCard>
                                    ))}
                                </SortableContext>
                            </ReportCanvas>
                        </Card>
                    </div>
                </div>

                <DragOverlay>
                    {activeDragType ? (
                        <div className="flex items-center gap-3 rounded-xl border border-indigo-300 bg-white px-4 py-3 shadow-lg dark:border-indigo-500 dark:bg-gray-800">
                            <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white">
                                <i className={`bx ${WIDGET_CATALOG[activeDragType].icon} text-lg`} />
                            </span>
                            <span className="text-sm font-medium text-gray-900 dark:text-white">
                                {t(WIDGET_CATALOG[activeDragType].labelKey)}
                            </span>
                        </div>
                    ) : null}
                </DragOverlay>
            </DndContext>
        </DashboardLayout>
    );
}
