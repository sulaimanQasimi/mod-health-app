export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedUsers {
    data: UserListItem[];
    links: PaginationLink[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
}

export interface UserListItem {
    id: number;
    name: string;
    email: string;
    avatar_url: string;
    category_name: string | null;
    is_doctor: boolean;
    clinic_type: string | null;
    status: number;
    roles: Array<{ id: number; name: string }>;
}

export interface UserIndexStats {
    active: number;
    inactive: number;
    total: number;
    new_this_month: number;
}

export interface UserIndexFilters {
    search: string;
    category_id: string;
    status: string;
    role_id: string;
    is_doctor: string;
    clinic_type: string;
    per_page: string;
}

export interface UserIndexFilterOptions {
    categories: Array<{ id: number; name: string }>;
    roles: Array<{ id: number; name: string; name_dr?: string | null }>;
}

export interface UserIndexPermissions {
    create: boolean;
    edit: boolean;
    toggleStatus: boolean;
}

export interface UserIndexUrls {
    index: string;
    create: string;
    edit: string;
    updateStatus: string;
}

export interface UserFormOption {
    id: number;
    name: string;
    name_dr?: string | null;
}

export interface UserDepartmentOption extends UserFormOption {
    branch_id: number;
}

export interface UserSectionOption extends UserFormOption {
    department_id: number;
}

export interface UserFormData {
    branches: UserFormOption[];
    departments: UserDepartmentOption[];
    sections: UserSectionOption[];
    categories: UserFormOption[];
    roles: UserFormOption[];
    permissions: UserFormOption[];
    clinicTypes: Array<{ value: string; label_key: string }>;
    defaultAvatar: string;
}

export interface UserFormValues {
    id?: number;
    name: string;
    last_name: string;
    email: string;
    branch_id: string;
    department_id: string;
    section_id: string;
    category_id: string;
    is_doctor: boolean;
    clinic_type: string;
    avatar_url: string;
    roles: string[];
    permissions: string[];
}

export interface UserFormUrls {
    index: string;
    store: string;
    update: string;
    back: string;
}

export interface UserProfile {
    id: number;
    name: string;
    last_name: string;
    email: string;
    avatar_url: string;
    status: number;
    is_doctor: boolean;
    clinic_type: string | null;
    branch_name: string | null;
    department_name: string | null;
    section_name: string | null;
    category_name: string | null;
    roles: Array<{ id: number; name: string }>;
    joined_at: string | null;
}

export interface ProfileUrls {
    update: string;
    updatePassword: string;
}
