export interface PersianDatepickerOptions {
    format?: string;
    autoClose?: boolean;
    initialValue?: boolean;
    observer?: boolean;
    calendar?: Record<string, unknown>;
    dayPicker?: {
        onSelect?: (unix: number) => void;
    };
    toolbox?: {
        todayButton?: {
            onToday?: () => void;
        };
    };
}

export interface PersianDatepickerInstance {
    destroy(): void;
    setDate(unix: number): void;
}

declare module 'jquery' {
    interface JQuery {
        val(): string | number | string[] | undefined;
        val(value: string): JQuery;
        on(events: string, handler?: () => void): JQuery;
        off(events?: string, handler?: () => void): JQuery;
        persianDatepicker(options?: PersianDatepickerOptions): PersianDatepickerInstance;
    }

    interface JQueryStatic {
        (element: Element): JQuery;
        fn: {
            persianDatepicker?: (options?: PersianDatepickerOptions) => PersianDatepickerInstance;
        };
    }

    const jQuery: JQueryStatic;
    export default jQuery;
}

declare global {
    interface Window {
        $: import('jquery').default;
        jQuery: import('jquery').default;
        persianDate: new (input?: unknown) => {
            format(template: string): string;
        };
    }
}

export {};
