export interface SidebarMenuItem {
    key: string;
    label: string;
    labelParts?: string[] | null;
    icon: string | null;
    route: string | null;
    href: string | null;
    activePatterns?: string[];
    children: SidebarMenuItem[];
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    avatar: string;
}

export interface SharedPageProps extends Record<string, unknown> {
    locale: string;
    direction: 'ltr' | 'rtl';
    translations: Record<string, unknown>;
    activityLogTranslations?: Record<string, unknown>;
    sidebarMenu: SidebarMenuItem[];
    currentRoute: string | null;
    auth: {
        user: AuthUser | null;
    };
    csrfToken: string;
    appUrls: {
        changeLanguage: string;
        profile: string;
        logout: string;
        login: string;
        chats: string;
    };
}
