import { PaginatedResult, SettingsFormUrls, SettingsPermissions } from './settings';

export interface PermissionItem {
    id: number;
    name: string;
    name_dr: string | null;
    parent_name: string | null;
}

export interface PermissionOption {
    id: number;
    name: string;
    name_dr: string | null;
}

export interface PermissionRecord {
    id: number;
    name: string;
    name_dr: string | null;
    parent_id: number | null;
}

export interface PermissionFormData {
    parentOptions: PermissionOption[];
}

export interface PermissionIndexProps {
    permissionsList: PaginatedResult<PermissionItem>;
    filters: { search: string; per_page: string };
    permissions: SettingsPermissions;
    urls: { index: string; create: string; edit: string };
}

export interface PermissionCreateEditProps {
    formData: PermissionFormData;
    urls: SettingsFormUrls;
    permission?: PermissionRecord;
}
