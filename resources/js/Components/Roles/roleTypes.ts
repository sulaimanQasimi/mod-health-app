export interface PermissionNode {
    id: number;
    name: string;
    name_dr: string | null;
    children: PermissionNode[];
}

export interface RoleFormData {
    permissionTree: PermissionNode[];
}

export interface RoleRecord {
    id: number;
    name: string;
    name_dr: string | null;
    permission_ids: number[];
}
