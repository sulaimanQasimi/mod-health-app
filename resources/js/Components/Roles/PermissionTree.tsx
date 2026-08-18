import { Checkbox, Label, TextInput } from 'flowbite-react';
import { useMemo, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { PermissionNode } from './roleTypes';

interface PermissionTreeProps {
    nodes: PermissionNode[];
    selectedIds: number[];
    onChange: (ids: number[]) => void;
}

function collectIds(nodes: PermissionNode[]): number[] {
    return nodes.flatMap((node) => [node.id, ...collectIds(node.children)]);
}

function nodeLabel(node: PermissionNode, locale: string): string {
    return locale === 'en' ? node.name : node.name_dr || node.name;
}

function matchesQuery(node: PermissionNode, query: string, locale: string): boolean {
    if (!query) {
        return true;
    }
    if (nodeLabel(node, locale).toLowerCase().includes(query)) {
        return true;
    }
    return node.children.some((child) => matchesQuery(child, query, locale));
}

function PermissionNodeItem({
    node,
    selectedIds,
    onToggle,
    locale,
    query,
}: {
    node: PermissionNode;
    selectedIds: number[];
    onToggle: (id: number, checked: boolean) => void;
    locale: string;
    query: string;
}) {
    const hasChildren = node.children.length > 0;
    const [open, setOpen] = useState(true);
    const checked = selectedIds.includes(node.id);
    const visibleChildren = node.children.filter((child) => matchesQuery(child, query, locale));

    return (
        <li>
            <div className="flex items-start gap-2 py-1">
                {hasChildren ? (
                    <button
                        type="button"
                        className="mt-0.5 text-indigo-500"
                        onClick={() => setOpen((value) => !value)}
                        aria-label={open ? 'collapse' : 'expand'}
                    >
                        <i className={`bx ${open ? 'bx-folder-open' : 'bx-folder'} text-lg`} />
                    </button>
                ) : (
                    <span className="mt-0.5 text-emerald-600">
                        <i className="bx bx-folder text-lg" />
                    </span>
                )}
                <label className="flex items-center gap-2">
                    <Checkbox checked={checked} onChange={(event) => onToggle(node.id, event.target.checked)} />
                    <span className="text-sm text-gray-800 dark:text-gray-100">{nodeLabel(node, locale)}</span>
                </label>
            </div>
            {hasChildren && open && visibleChildren.length > 0 ? (
                <ul className="ms-6 border-s border-gray-200 ps-3 dark:border-gray-700">
                    {visibleChildren.map((child) => (
                        <PermissionNodeItem
                            key={child.id}
                            node={child}
                            selectedIds={selectedIds}
                            onToggle={onToggle}
                            locale={locale}
                            query={query}
                        />
                    ))}
                </ul>
            ) : null}
        </li>
    );
}

export default function PermissionTree({ nodes, selectedIds, onChange }: PermissionTreeProps) {
    const { t, locale } = useTranslation();
    const [query, setQuery] = useState('');
    const allIds = useMemo(() => collectIds(nodes), [nodes]);
    const normalizedQuery = query.trim().toLowerCase();
    const visibleNodes = useMemo(
        () => nodes.filter((node) => matchesQuery(node, normalizedQuery, locale)),
        [nodes, normalizedQuery, locale],
    );
    const allSelected = allIds.length > 0 && allIds.every((id) => selectedIds.includes(id));

    const toggleId = (id: number, checked: boolean) => {
        if (checked) {
            onChange(selectedIds.includes(id) ? selectedIds : [...selectedIds, id]);
            return;
        }
        onChange(selectedIds.filter((selected) => selected !== id));
    };

    return (
        <div className="space-y-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                    {t('global.permissions_list')}
                </h3>
                <label className="flex items-center gap-2">
                    <Checkbox
                        checked={allSelected}
                        onChange={(event) => onChange(event.target.checked ? allIds : [])}
                    />
                    <span className="text-sm font-medium text-blue-600">{t('global.select_all')}</span>
                </label>
            </div>
            <div>
                <Label htmlFor="permission-search">{t('global.search')}</Label>
                <TextInput
                    id="permission-search"
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                />
            </div>
            <p className="text-xs text-gray-500">
                {selectedIds.length} / {allIds.length}
            </p>
            <ul className="max-h-[28rem] overflow-y-auto pe-2">
                {visibleNodes.map((node) => (
                    <PermissionNodeItem
                        key={node.id}
                        node={node}
                        selectedIds={selectedIds}
                        onToggle={toggleId}
                        locale={locale}
                        query={normalizedQuery}
                    />
                ))}
            </ul>
        </div>
    );
}
