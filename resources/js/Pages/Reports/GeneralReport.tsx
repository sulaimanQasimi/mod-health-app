import {
    DndContext,
    DragEndEvent,
    DragOverlay,
    DragStartEvent,
    PointerSensor,
    pointerWithin,
    rectIntersection,
    useDraggable,
    useDroppable,
    useSensor,
    useSensors,
    type CollisionDetection,
} from '@dnd-kit/core';
import {
    SortableContext,
    arrayMove,
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { Head, router } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, ReactNode, useEffect, useMemo, useState } from 'react';
import '../../../css/general-report-print.css';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import PersianDateInput from '../../Components/ui/PersianDateInput';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_INDEX_WIDTH } from '../../utils/settingsUi';
import NumberOfPatientTestRegistrations from './CustomHooks/NumberOfPatientTestRegistrations';
import NumberOfPrescriptions from './CustomHooks/NumberOfPrescriptions';
import NumberOfHospitalizationsBaseOnDepartment from './CustomHooks/NumberOfHospitalizationsBaseOnDepartment';
import NumberOfPatientsBaseOnDepartment from './CustomHooks/NumberOfPatientsBaseOnDepartment';
import NumberOfPatientsBaseOnPatientMiliteryTypes from './CustomHooks/NumberOfPatientsBaseOnPatientMiliteryTypes';
import GeneralReportHeader from './CustomHooks/GeneralReportHeader';
import GeneralReportTitle from './CustomHooks/GeneralReportTitle';
import { createDefaultHeaderSettings, ReportHeaderSettings } from './CustomHooks/generalReportHeaderSettings';
import { createDefaultTitleSettings, ReportTitleSettings } from './CustomHooks/generalReportTitleSettings';

type ReportWidgetType =
    | 'department'
    | 'militery-types'
    | 'hospitalization'
    | 'patient-test-registrations'
    | 'prescriptions'
    | 'header'
    | 'title';

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
    headerSettings?: ReportHeaderSettings;
    titleSettings?: ReportTitleSettings;
}

interface Slot {
    id: string;
    colSpan: 1 | 2 | 3 | 4;
}

const SLOT_COL_SPAN_OPTIONS: Array<Slot['colSpan']> = [1, 2, 3, 4];
const DEFAULT_SLOT_HEIGHT = 320;

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
    hospitalization: {
        labelKey: 'global.hospitalization',
        icon: 'bx-bed',
    },
    'patient-test-registrations': {
        labelKey: 'global.patient_test_registrations',
        icon: 'bx-test-tube',
    },
    prescriptions: {
        labelKey: 'global.prescriptions',
        icon: 'bx-capsule',
    },
    header: {
        labelKey: 'global.header',
        icon: 'bx-id-card',
    },
    title: {
        labelKey: 'global.title',
        icon: 'bx-text',
    },
};

const INITIAL_SLOT_ID = 'slot-default';

function createWidget(type: ReportWidgetType): PlacedWidget {
    const widget: PlacedWidget = {
        id: `${type}-${Date.now()}`,
        type,
    };

    if (type === 'header') {
        widget.headerSettings = createDefaultHeaderSettings();
    }

    if (type === 'title') {
        widget.titleSettings = createDefaultTitleSettings();
    }

    return widget;
}

const canvasCollisionDetection: CollisionDetection = (args) => {
    const pointerCollisions = pointerWithin(args);
    if (pointerCollisions.length > 0) {
        return pointerCollisions;
    }

    return rectIntersection(args);
};

function createSlot(): Slot {
    return {
        id: `slot-${Date.now()}`,
        colSpan: 1,
    };
}

function createInitialCanvasState(): { slots: Slot[]; widgetsBySlot: Record<string, PlacedWidget[]> } {
    const firstSlot: Slot = { id: INITIAL_SLOT_ID, colSpan: 1 };

    return {
        slots: [firstSlot],
        widgetsBySlot: { [firstSlot.id]: [] },
    };
}

function slotColSpanClass(colSpan: Slot['colSpan']): string {
    switch (colSpan) {
        case 1:
            return 'col-span-1 xl:col-span-1';
        case 2:
            return 'col-span-1 xl:col-span-2';
        case 3:
            return 'col-span-1 xl:col-span-3';
        case 4:
            return 'col-span-1 xl:col-span-4';
        default:
            return 'col-span-1 xl:col-span-1';
    }
}

function PaletteItem({
    type,
    label,
    icon,
    onAdd,
    isPlaced,
    addedLabel,
}: {
    type: ReportWidgetType;
    label: string;
    icon: string;
    onAdd: (type: ReportWidgetType) => void;
    isPlaced: boolean;
    addedLabel: string;
}) {
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id: `palette-${type}`,
        data: { source: 'palette', type },
        disabled: isPlaced,
    });

    const style = transform ? { transform: CSS.Translate.toString(transform) } : undefined;

    return (
        <div
            className={`flex w-full items-center gap-3 rounded-xl border px-3 py-2 text-start text-sm font-medium shadow-sm transition ${isPlaced
                    ? 'border-emerald-200 bg-emerald-50/80 text-gray-600 dark:border-emerald-900 dark:bg-emerald-950/20 dark:text-gray-300'
                    : 'border-gray-200 bg-white text-gray-800 hover:border-indigo-300 hover:bg-indigo-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/30'
                }`}
        >
            <button
                type="button"
                onClick={() => onAdd(type)}
                disabled={isPlaced}
                className="flex min-w-0 flex-1 items-center gap-3 disabled:cursor-not-allowed"
            >
                <span
                    className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white ${isPlaced
                            ? 'bg-emerald-500'
                            : 'bg-gradient-to-br from-indigo-500 to-violet-600'
                        }`}
                >
                    <i className={`bx ${isPlaced ? 'bx-check' : icon} text-lg`} />
                </span>
                <span className="flex-1">{label}</span>
                {isPlaced ? (
                    <span className="text-xs font-medium text-emerald-600 dark:text-emerald-400">{addedLabel}</span>
                ) : (
                    <i className="bx bx-plus text-lg text-indigo-500" />
                )}
            </button>
            <button
                ref={setNodeRef}
                style={style}
                type="button"
                disabled={isPlaced}
                className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-400 dark:border-gray-700 ${isPlaced
                        ? 'cursor-not-allowed opacity-40'
                        : 'cursor-grab hover:bg-gray-50 active:cursor-grabbing dark:hover:bg-gray-800'
                    } ${isDragging ? 'opacity-50' : ''}`}
                aria-label={`Drag ${label}`}
                {...listeners}
                {...attributes}
            >
                <i className="bx bx-move text-lg" />
            </button>
        </div>
    );
}

function SlotCanvas({
    slotId,
    children,
    isEmpty,
    emptyLabel,
}: {
    slotId: string;
    children: ReactNode;
    isEmpty: boolean;
    emptyLabel: string;
}) {
    const { setNodeRef, isOver } = useDroppable({ id: slotId });

    return (
        <div
            ref={setNodeRef}
            style={{ minHeight: DEFAULT_SLOT_HEIGHT }}
            className={`general-report-slot-canvas relative h-full rounded-2xl border-2 border-dashed p-4 transition ${isOver
                    ? 'border-indigo-400 bg-indigo-50/70 dark:border-indigo-500 dark:bg-indigo-950/20'
                    : 'border-gray-200 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-900/40'
                }`}
        >
            {isEmpty && (
                <div className="general-report-no-print pointer-events-none absolute inset-0 flex flex-col items-center justify-center gap-3 p-4 text-center text-gray-500 dark:text-gray-400">
                    <div className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/30">
                        <i className="bx bx-layer-plus text-2xl text-indigo-500" />
                    </div>
                    <p className="max-w-md text-sm">{emptyLabel}</p>
                </div>
            )}
            <div className={`relative h-full space-y-4 overflow-auto ${isEmpty ? 'min-h-[200px]' : ''}`}>
                {children}
            </div>
        </div>
    );
}

function ReportSlot({
    slot,
    index,
    canRemove,
    canMoveUp,
    canMoveDown,
    widgetCount,
    slotLabel,
    orderLabel,
    widthLabel,
    removeLabel,
    isExpanded,
    onToggle,
    onRemove,
    onMoveUp,
    onMoveDown,
    onColSpanChange,
    children,
}: {
    slot: Slot;
    index: number;
    canRemove: boolean;
    canMoveUp: boolean;
    canMoveDown: boolean;
    widgetCount: number;
    slotLabel: string;
    orderLabel: string;
    widthLabel: string;
    removeLabel: string;
    isExpanded: boolean;
    onToggle: () => void;
    onRemove: (slotId: string) => void;
    onMoveUp: (slotId: string) => void;
    onMoveDown: (slotId: string) => void;
    onColSpanChange: (slotId: string, colSpan: Slot['colSpan']) => void;
    children: ReactNode;
}) {
    return (
        <div
            className={`general-report-slot flex min-w-0 flex-col ${slotColSpanClass(slot.colSpan)} ${!isExpanded ? 'general-report-slot-collapsed' : ''} ${widgetCount === 0 ? 'general-report-no-print' : ''}`}
        >
            <div className="general-report-no-print mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <button
                    type="button"
                    onClick={onToggle}
                    className="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-start transition hover:bg-gray-50 dark:hover:bg-gray-700/40"
                    aria-expanded={isExpanded}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            {index + 1}
                        </span>
                        <p className="text-sm font-semibold text-gray-900 dark:text-white">{slotLabel}</p>
                        {widgetCount > 0 && (
                            <Badge color="indigo" size="sm">
                                {widgetCount}
                            </Badge>
                        )}
                    </div>
                    <i
                        className={`bx ${isExpanded ? 'bx-chevron-up' : 'bx-chevron-down'} shrink-0 text-xl text-gray-400`}
                    />
                </button>

                {isExpanded && (
                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs text-gray-500 dark:text-gray-400">{orderLabel}</span>
                            <div className="flex items-center gap-1">
                                <Button
                                    type="button"
                                    size="xs"
                                    color="light"
                                    onClick={() => onMoveUp(slot.id)}
                                    disabled={!canMoveUp}
                                    title={`${orderLabel} up`}
                                    className="!min-w-8"
                                >
                                    <i className="bx bx-up-arrow-alt" />
                                </Button>
                                <Button
                                    type="button"
                                    size="xs"
                                    color="light"
                                    onClick={() => onMoveDown(slot.id)}
                                    disabled={!canMoveDown}
                                    title={`${orderLabel} down`}
                                    className="!min-w-8"
                                >
                                    <i className="bx bx-down-arrow-alt" />
                                </Button>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs text-gray-500 dark:text-gray-400">{widthLabel}</span>
                            <div className="flex items-center gap-1 rounded-lg bg-gray-50 p-0.5 dark:bg-gray-900/60">
                                {SLOT_COL_SPAN_OPTIONS.map((span) => (
                                    <Button
                                        key={span}
                                        type="button"
                                        size="xs"
                                        color={slot.colSpan === span ? 'blue' : 'light'}
                                        onClick={() => onColSpanChange(slot.id, span)}
                                        title={`${widthLabel} ${span}`}
                                        className="!min-w-8"
                                    >
                                        {span}
                                    </Button>
                                ))}
                            </div>
                            <Button
                                type="button"
                                color="light"
                                size="xs"
                                onClick={() => onRemove(slot.id)}
                                disabled={!canRemove}
                                title={removeLabel}
                            >
                                <i className="bx bx-trash" />
                            </Button>
                        </div>
                    </div>
                )}
            </div>

            <div className="general-report-slot-content min-h-0 flex-1">{children}</div>
        </div>
    );
}

function SortableWidgetCard({
    widget,
    slotId,
    title,
    removeLabel,
    onRemove,
    children,
}: {
    widget: PlacedWidget;
    slotId: string;
    title: string;
    removeLabel: string;
    onRemove: (id: string) => void;
    children: ReactNode;
}) {
    const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
        id: widget.id,
        data: { source: 'canvas', type: widget.type, slotId, widgetId: widget.id },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div ref={setNodeRef} style={style} className="general-report-widget-wrap">
            <Card className="general-report-widget !shadow-sm">
                <div className="general-report-no-print mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
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
                        <i className="bx bx-trash" />
                    </Button>
                </div>
                {children}
            </Card>
        </div>
    );
}

function WidgetSearchPrompt({ title, message }: { title: string; message: string }) {
    return (
        <div className="general-report-no-print flex min-h-[180px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-amber-200 bg-amber-50/60 px-6 py-8 text-center dark:border-amber-900/60 dark:bg-amber-950/20">
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/40">
                <i className="bx bx-filter-alt text-2xl text-amber-600 dark:text-amber-400" />
            </div>
            <div>
                <p className="text-sm font-semibold text-gray-900 dark:text-white">{title}</p>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">{message}</p>
            </div>
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
    const [initialCanvas] = useState(createInitialCanvasState);
    const [slots, setSlots] = useState<Slot[]>(initialCanvas.slots);
    const [widgetsBySlot, setWidgetsBySlot] = useState<Record<string, PlacedWidget[]>>(initialCanvas.widgetsBySlot);
    const [activeDragType, setActiveDragType] = useState<ReportWidgetType | null>(null);
    const [isDndReady, setIsDndReady] = useState(false);
    const [expandedSlots, setExpandedSlots] = useState<Record<string, boolean>>({});

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: { distance: 8 },
        }),
    );

    useEffect(() => {
        setIsDndReady(true);
    }, []);

    useEffect(() => {
        setFilters(serverFilters);
        setHasSearch(serverHasSearch);
    }, [serverFilters, serverHasSearch]);

    const appliedFilters = hasSearch ? filters : EMPTY_FILTERS;

    const placedWidgetTypes = useMemo(() => {
        const types = new Set<ReportWidgetType>();
        Object.values(widgetsBySlot).forEach((list) => {
            list.forEach((widget) => types.add(widget.type));
        });
        return types;
    }, [widgetsBySlot]);

    const totalWidgetCount = useMemo(
        () => Object.values(widgetsBySlot).reduce((sum, list) => sum + list.length, 0),
        [widgetsBySlot],
    );

    const branchLabel = useMemo(() => {
        if (!filters.branch_id) {
            return t('global.all');
        }
        return filterOptions.branches.find((branch) => String(branch.id) === filters.branch_id)?.name ?? filters.branch_id;
    }, [filterOptions.branches, filters.branch_id, t]);

    const hasWidgetTypeInAnySlot = (type: ReportWidgetType, current: Record<string, PlacedWidget[]>) =>
        type !== 'title' &&
        Object.values(current).some((list) => list.some((w) => w.type === type));

    const resolveTargetSlotId = () => slots[slots.length - 1]?.id;

    const addWidgetToSlot = (type: ReportWidgetType, slotId: string) => {
        if (!slotId) {
            return;
        }

        setWidgetsBySlot((current) => {
            const next = { ...current };
            const list = next[slotId] ? [...next[slotId]] : [];

            if (hasWidgetTypeInAnySlot(type, next)) {
                return current;
            }

            list.push(createWidget(type));
            next[slotId] = list;
            return next;
        });
    };

    const removeWidget = (id: string) => {
        setWidgetsBySlot((current) => {
            const next: Record<string, PlacedWidget[]> = {};
            for (const [slotId, list] of Object.entries(current)) {
                next[slotId] = list.filter((widget) => widget.id !== id);
            }
            return next;
        });
    };

    const updateWidgetHeaderSettings = (widgetId: string, headerSettings: ReportHeaderSettings) => {
        setWidgetsBySlot((current) => {
            const next: Record<string, PlacedWidget[]> = {};
            for (const [slotId, list] of Object.entries(current)) {
                next[slotId] = list.map((widget) =>
                    widget.id === widgetId ? { ...widget, headerSettings } : widget,
                );
            }
            return next;
        });
    };

    const updateWidgetTitleSettings = (widgetId: string, titleSettings: ReportTitleSettings) => {
        setWidgetsBySlot((current) => {
            const next: Record<string, PlacedWidget[]> = {};
            for (const [slotId, list] of Object.entries(current)) {
                next[slotId] = list.map((widget) =>
                    widget.id === widgetId ? { ...widget, titleSettings } : widget,
                );
            }
            return next;
        });
    };

    const addSlot = () => {
        const newSlot = createSlot();
        setSlots((current) => [newSlot, ...current]);
        setWidgetsBySlot((current) => ({ ...current, [newSlot.id]: [] }));
        setExpandedSlots((current) => ({ ...current, [newSlot.id]: true }));
    };

    const removeSlot = (slotId: string) => {
        setSlots((current) => {
            if (current.length <= 1) return current;
            return current.filter((slot) => slot.id !== slotId);
        });
        setWidgetsBySlot((current) => {
            const next = { ...current };
            delete next[slotId];
            return next;
        });
        setExpandedSlots((current) => {
            const next = { ...current };
            delete next[slotId];
            return next;
        });
    };

    const setSlotColSpan = (slotId: string, colSpan: Slot['colSpan']) => {
        setSlots((current) =>
            current.map((slot) => (slot.id === slotId ? { ...slot, colSpan } : slot)),
        );
    };

    const moveSlot = (slotId: string, direction: 'up' | 'down') => {
        setSlots((current) => {
            const index = current.findIndex((slot) => slot.id === slotId);
            if (index === -1) {
                return current;
            }

            const targetIndex = direction === 'up' ? index - 1 : index + 1;
            if (targetIndex < 0 || targetIndex >= current.length) {
                return current;
            }

            return arrayMove(current, index, targetIndex);
        });
    };

    const isSlotExpanded = (slotId: string) => expandedSlots[slotId] ?? true;

    const toggleSlot = (slotId: string) => {
        setExpandedSlots((current) => ({
            ...current,
            [slotId]: !isSlotExpanded(slotId),
        }));
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
        setHasSearch(false);
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

        const activeData = active.data.current as {
            source?: string;
            type?: ReportWidgetType;
            slotId?: string;
        };
        const overData = over.data.current as {
            source?: string;
            slotId?: string;
            widgetId?: string;
        } | undefined;

        const overId = String(over.id);
        const isSlotDrop = overId.startsWith('slot-');
        const isCanvasDrop = overData?.source === 'canvas';

        if (activeData?.source === 'palette' && activeData.type) {
            if (isSlotDrop) {
                addWidgetToSlot(activeData.type, overId);
            } else if (isCanvasDrop && overData?.slotId) {
                addWidgetToSlot(activeData.type, overData.slotId);
            }

            return;
        }

        if (activeData?.source === 'canvas' && active.id !== over.id) {
            const fromSlot = activeData.slotId;
            if (!fromSlot) {
                return;
            }

            if (isSlotDrop) {
                const toSlot = overId;
                if (toSlot === fromSlot) {
                    return;
                }

                setWidgetsBySlot((current) => {
                    const fromList = current[fromSlot] ?? [];
                    const toList = current[toSlot] ?? [];
                    const moving = fromList.find((w) => w.id === active.id);
                    if (!moving) {
                        return current;
                    }

                    return {
                        ...current,
                        [fromSlot]: fromList.filter((w) => w.id !== active.id),
                        [toSlot]: [...toList, moving],
                    };
                });

                return;
            }

            if (isCanvasDrop && overData?.slotId) {
                const toSlot = overData.slotId;
                const overWidgetId = overData.widgetId ?? overId;

                setWidgetsBySlot((current) => {
                    const fromList = current[fromSlot] ?? [];
                    const toList = current[toSlot] ?? [];
                    const moving = fromList.find((w) => w.id === active.id);
                    if (!moving) {
                        return current;
                    }

                    const withoutMoving = fromList.filter((w) => w.id !== active.id);

                    const insertIndex = toList.findIndex((w) => w.id === overWidgetId);
                    const nextTo =
                        insertIndex === -1
                            ? [...toList, moving]
                            : [...toList.slice(0, insertIndex), moving, ...toList.slice(insertIndex)];

                    if (fromSlot === toSlot) {
                        const oldIndex = toList.findIndex((w) => w.id === active.id);
                        const newIndex = toList.findIndex((w) => w.id === overWidgetId);
                        if (oldIndex === -1 || newIndex === -1) {
                            return current;
                        }
                        return { ...current, [toSlot]: arrayMove(toList, oldIndex, newIndex) };
                    }

                    return {
                        ...current,
                        [fromSlot]: withoutMoving,
                        [toSlot]: nextTo,
                    };
                });
            }
        }
    };

    const renderWidget = (widget: PlacedWidget) => {
        if (widget.type === 'header') {
            return (
                <GeneralReportHeader
                    settings={widget.headerSettings ?? createDefaultHeaderSettings()}
                    onSettingsChange={(headerSettings) =>
                        updateWidgetHeaderSettings(widget.id, headerSettings)
                    }
                />
            );
        }

        if (widget.type === 'title') {
            return (
                <GeneralReportTitle
                    settings={widget.titleSettings ?? createDefaultTitleSettings()}
                    onSettingsChange={(titleSettings) =>
                        updateWidgetTitleSettings(widget.id, titleSettings)
                    }
                />
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

        if (widget.type === 'hospitalization') {
            return <NumberOfHospitalizationsBaseOnDepartment {...filterProps} />;
        }

        if (widget.type === 'patient-test-registrations') {
            return <NumberOfPatientTestRegistrations {...filterProps} />;
        }

        if (widget.type === 'prescriptions') {
            return <NumberOfPrescriptions {...filterProps} />;
        }

        return <NumberOfPatientsBaseOnPatientMiliteryTypes {...filterProps} />;
    };

    const renderReportCanvas = () => (
        <>
            <Card className="general-report-no-print !shadow-sm">
                <div className="mb-4 border-b border-gray-100 pb-4 dark:border-gray-700">
                    <div className="flex items-start gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950/40">
                            <i className="bx bx-widget text-xl text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                {t('global.reports')}
                            </h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('global.general')} — click or drag a component into any slot below.
                            </p>
                        </div>
                    </div>
                </div>
                <div className="grid gap-3 sm:grid-cols-2">
                    {(Object.keys(WIDGET_CATALOG) as ReportWidgetType[]).map((type) => (
                        <PaletteItem
                            key={type}
                            type={type}
                            label={t(WIDGET_CATALOG[type].labelKey)}
                            icon={WIDGET_CATALOG[type].icon}
                            isPlaced={type !== 'title' && placedWidgetTypes.has(type)}
                            addedLabel="Added"
                            onAdd={(widgetType) => {
                                const targetSlotId = resolveTargetSlotId();
                                if (targetSlotId) {
                                    addWidgetToSlot(widgetType, targetSlotId);
                                }
                            }}
                        />
                    ))}
                </div>
            </Card>

            <Card className="general-report-slots !shadow-sm">
                <div className="general-report-no-print mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                    <div className="flex items-start gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-950/40">
                            <i className="bx bx-layout text-xl text-violet-600 dark:text-violet-400" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-gray-900 dark:text-white">
                                {t('global.general')}
                            </h2>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                New slots appear at the top. Adjust width to span 1–4 columns on large screens.
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button type="button" color="light" size="sm" onClick={() => window.print()}>
                            <i className="bx bx-printer" />
                        </Button>
                        <Button type="button" color="blue" size="sm" onClick={addSlot}>
                            <i className="bx bx-plus" />
                        </Button>
                    </div>
                </div>

                <div className="general-report-slot-grid grid grid-cols-1 gap-4 xl:grid-cols-4">
                    {slots.map((slot, index) => {
                        const widgets = widgetsBySlot[slot.id] ?? [];
                        const widgetIds = widgets.map((w) => w.id);

                        return (
                            <ReportSlot
                                key={slot.id}
                                slot={slot}
                                index={index}
                                canRemove={slots.length > 1}
                                canMoveUp={index > 0}
                                canMoveDown={index < slots.length - 1}
                                widgetCount={widgets.length}
                                slotLabel={`Slot ${index + 1}`}
                                orderLabel={t('global.order')}
                                widthLabel="Width"
                                removeLabel={t('global.remove')}
                                isExpanded={isSlotExpanded(slot.id)}
                                onToggle={() => toggleSlot(slot.id)}
                                onRemove={removeSlot}
                                onMoveUp={(slotId) => moveSlot(slotId, 'up')}
                                onMoveDown={(slotId) => moveSlot(slotId, 'down')}
                                onColSpanChange={setSlotColSpan}
                            >
                                <SlotCanvas
                                    slotId={slot.id}
                                    isEmpty={widgets.length === 0}
                                    emptyLabel="Drop components here"
                                >
                                    <SortableContext items={widgetIds} strategy={verticalListSortingStrategy}>
                                        {widgets.map((widget) => {
                                            const isInstantWidget =
                                                widget.type === 'header' || widget.type === 'title';
                                            const showWidget = isInstantWidget || hasSearch;

                                            return (
                                                <div
                                                    key={widget.id}
                                                    data-slot={slot.id}
                                                    className={!showWidget ? 'general-report-no-print' : undefined}
                                                >
                                                    <SortableWidgetCard
                                                        widget={widget}
                                                        slotId={slot.id}
                                                        title={t(WIDGET_CATALOG[widget.type].labelKey)}
                                                        removeLabel={t('global.remove')}
                                                        onRemove={removeWidget}
                                                    >
                                                        {showWidget ? (
                                                            renderWidget(widget)
                                                        ) : (
                                                            <WidgetSearchPrompt
                                                                title={t('global.search')}
                                                                message="Set your filters above and click Search to load report data."
                                                            />
                                                        )}
                                                    </SortableWidgetCard>
                                                </div>
                                            );
                                        })}
                                    </SortableContext>
                                </SlotCanvas>
                            </ReportSlot>
                        );
                    })}
                </div>
            </Card>
        </>
    );

    return (
        <DashboardLayout>
            <Head title={t('global.reports')} />

            <div className={`general-report-page mx-auto space-y-5 ${SETTINGS_INDEX_WIDTH.wide}`}>
                <div className="general-report-no-print">
                    <SettingsPageHeader
                        title={t('global.reports')}
                        subtitle={t('global.general')}
                        icon="bx-bar-chart-alt-2"
                        accent="from-indigo-600 to-violet-700"
                        backLabel={t('global.back')}
                    />
                </div>

                {hasSearch && (
                    <Card className="general-report-no-print !shadow-sm">
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white">
                                <i className="bx bx-check-shield text-lg" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-semibold text-gray-900 dark:text-white">
                                    {t('global.advanced_filters')}
                                </p>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    <Badge color="indigo">
                                        {t('global.branch')}: {branchLabel}
                                    </Badge>
                                    {filters.date_from && (
                                        <Badge color="gray">
                                            {t('global.from')}: {filters.date_from}
                                        </Badge>
                                    )}
                                    {filters.date_to && (
                                        <Badge color="gray">
                                            {t('global.to')}: {filters.date_to}
                                        </Badge>
                                    )}
                                    {!filters.date_from && !filters.date_to && (
                                        <Badge color="gray">{t('global.all')}</Badge>
                                    )}
                                </div>
                            </div>
                            {totalWidgetCount > 0 && (
                                <div className="text-end">
                                    <p className="text-xs text-gray-500 dark:text-gray-400">{t('global.total')}</p>
                                    <p className="text-lg font-bold text-gray-900 dark:text-white">{totalWidgetCount}</p>
                                </div>
                            )}
                        </div>
                    </Card>
                )}

                <Card className="general-report-no-print !shadow-sm">
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

                {isDndReady ? (
                    <DndContext
                        id="general-report-canvas"
                        sensors={sensors}
                        collisionDetection={canvasCollisionDetection}
                        onDragStart={handleDragStart}
                        onDragEnd={handleDragEnd}
                    >
                        <div className="space-y-5">{renderReportCanvas()}</div>
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
                ) : (
                    <div className="general-report-no-print space-y-5" aria-hidden>
                        <Card className="!shadow-sm">
                            <div className="h-28 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800" />
                        </Card>
                        <Card className="!shadow-sm">
                            <div className="h-80 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800" />
                        </Card>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
}
