export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface PaginatedResult<T> {
    data: T[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface SettingsPermissions {
    create: boolean;
    edit: boolean;
    delete: boolean;
    view?: boolean;
}

export interface OptionItem {
    id: number;
    name: string;
}

export interface SettingsFormUrls {
    index: string;
    store: string;
    update: string;
    back: string;
}
