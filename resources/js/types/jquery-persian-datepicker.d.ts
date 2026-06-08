export interface PersianDatepickerOptions {
    months?: string[];
    dowTitle?: string[];
    shortDowTitle?: string[];
    formatDate?: string;
    showGregorianDate?: boolean;
    persianNumbers?: boolean;
    theme?: string;
    closeOnBlur?: boolean;
    onSelect?: () => void;
}

export interface PersianDatepickerInstance {
    calendar?: {
        remove(): void;
    };
}

declare module 'jquery' {
    interface JQuery {
        val(): string | number | string[] | undefined;
        val(value: string): JQuery;
        on(events: string, handler?: () => void): JQuery;
        off(events?: string, handler?: () => void): JQuery;
        removeClass(className: string): JQuery;
        removeAttr(attr: string): JQuery;
        removeData(key?: string): JQuery;
        data(key: 'persianDatepicker'): PersianDatepickerInstance | undefined;
        persianDatepicker(options?: PersianDatepickerOptions): JQuery;
    }

    interface JQueryStatic {
        (element: Element): JQuery;
        fn: {
            persianDatepicker?: (options?: PersianDatepickerOptions) => JQuery;
        };
    }

    const jQuery: JQueryStatic;
    export default jQuery;
}

declare global {
    interface Window {
        $: import('jquery').default;
        jQuery: import('jquery').default;
    }
}

export {};
