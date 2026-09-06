import { Link, usePage } from '@inertiajs/react';
import { Dropdown, DropdownDivider, DropdownHeader, DropdownItem } from 'flowbite-react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';
import ThemeToggle from './ThemeToggle';

const languages = [
    { code: 'en', label: 'English' },
    { code: 'dr', label: 'دری' },
    { code: 'ps', label: 'پشتو' },
];

interface NavbarProps {
    onMenuToggle: () => void;
    sidebarOpen: boolean;
}

function resolveCsrfToken(sharedToken?: string): string {
    if (sharedToken) {
        return sharedToken;
    }

    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

export default function Navbar({ onMenuToggle, sidebarOpen }: NavbarProps) {
    const { auth, appUrls, csrfToken } = usePage<SharedPageProps>().props;
    const { t } = useTranslation();
    const user = auth.user;

    const handleLogout = () => {
        const logoutUrl = appUrls?.logout || '/logout';
        const token = resolveCsrfToken(csrfToken);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = logoutUrl;
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = token;
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    };

    return (
        <header className="sticky top-0 z-20 border-b border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800">
            <div className="flex items-center gap-3">
                <button
                    type="button"
                    className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-100 lg:hidden dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
                    aria-label="Open menu"
                    aria-expanded={sidebarOpen}
                    onClick={onMenuToggle}
                >
                    <i className="bx bx-menu text-2xl" />
                </button>

                <div className="ms-auto flex min-w-0 items-center justify-end gap-2 sm:gap-3">
                    {user && (
                        <a
                            href={appUrls.chats}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:hover:text-white"
                            aria-label={t('global.chats')}
                            title={t('global.chats')}
                        >
                            <i className="bx bx-message-dots text-xl" />
                        </a>
                    )}

                    <ThemeToggle />

                    <Dropdown
                        label=""
                        dismissOnClick={false}
                        renderTrigger={() => (
                            <button
                                type="button"
                                className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
                                aria-label="Change language"
                            >
                                <img src="/assets/img/icons/language-icon.png" alt="" className="h-7 w-7" />
                            </button>
                        )}
                    >
                        {languages.map((language) => (
                            <DropdownItem key={language.code} as="a" href={`${appUrls.changeLanguage}/${language.code}`}>
                                {language.label}
                            </DropdownItem>
                        ))}
                    </Dropdown>

                    {user && (
                        <Dropdown
                            arrowIcon={false}
                            inline
                            label=""
                            renderTrigger={() => (
                                <button
                                    type="button"
                                    className="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
                                >
                                    <img
                                        src={user.avatar}
                                        alt={user.name}
                                        className="h-9 w-9 rounded-full object-cover sm:h-10 sm:w-10"
                                    />
                                </button>
                            )}
                        >
                            <DropdownHeader>
                                <span className="block text-sm font-semibold">{user.name}</span>
                                <span className="block truncate text-sm text-gray-500">{user.email}</span>
                            </DropdownHeader>
                            <DropdownItem as={Link} href={appUrls.profile}>
                                <i className="bx bx-user me-2" />
                                {t('global.my_profile')}
                            </DropdownItem>
                            <DropdownDivider />
                            <DropdownItem as="button" type="button" onClick={handleLogout}>
                                <i className="bx bx-log-out me-2" />
                                {t('global.logout')}
                            </DropdownItem>
                        </Dropdown>
                    )}
                </div>
            </div>
        </header>
    );
}
