import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { SharedPageProps } from '../types';

export interface SectionPermissions {
    create?: boolean;
    edit?: boolean;
    delete?: boolean;
    view?: boolean;
}

export interface AppointmentSectionPayload<TItem = Record<string, unknown>> {
    items: TItem[];
    count: number;
    permissions: SectionPermissions;
    meta?: Record<string, unknown>;
    referral_remarks?: string | null;
    urls?: Record<string, string>;
}

export function useAppointmentSection<TItem = Record<string, unknown>>(
    appointmentId: number,
    sectionPath: string,
) {
    const { csrfToken } = usePage<SharedPageProps>().props;
    const baseUrl = `/appointments/${appointmentId}/${sectionPath}`;
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState<AppointmentSectionPayload<TItem> | null>(null);

    const reload = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(baseUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json();
            if (payload.success) {
                setData(payload.data);
            } else {
                setData({
                    items: [],
                    count: 0,
                    permissions: {},
                });
            }
        } catch {
            setData({
                items: [],
                count: 0,
                permissions: {},
            });
        } finally {
            setLoading(false);
        }
    }, [baseUrl]);

    useEffect(() => {
        reload();
    }, [reload]);

    const post = async (body: Record<string, unknown>, path = '') => {
        const response = await fetch(`${baseUrl}${path}`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });
        return response.json();
    };

    const put = async (path: string, body: Record<string, unknown>) => {
        const response = await fetch(`${baseUrl}${path}`, {
            method: 'PUT',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });
        return response.json();
    };

    const destroy = async (path: string) => {
        await fetch(`${baseUrl}${path}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        await reload();
    };

    return { loading, data, reload, baseUrl, csrfToken, post, put, destroy };
}
