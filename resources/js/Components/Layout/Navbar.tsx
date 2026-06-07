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

export default function Navbar() {
    const { auth, urls, csrfToken } = usePage<SharedPageProps>().props;
    const { t } = useTranslation();
    const user = auth.user;

    return (
        <header className="sticky top-0 z-20 border-b border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-800">
            <div className="flex items-center justify-end gap-3">
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
                        <DropdownItem key={language.code} as="a" href={`${urls.changeLanguage}/${language.code}`}>
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
                            <button type="button" className="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <img src={user.avatar} alt={user.name} className="h-10 w-10 rounded-full object-cover" />
                            </button>
                        )}
                    >
                        <DropdownHeader>
                            <span className="block text-sm font-semibold">{user.name}</span>
                            <span className="block truncate text-sm text-gray-500">{user.email}</span>
                        </DropdownHeader>
                        <DropdownItem as={Link} href={urls.profile}>
                            <i className="bx bx-user me-2" />
                            {t('global.my_profile')}
                        </DropdownItem>
                        <DropdownDivider />
                        <DropdownItem as="a" href={urls.logout} onClick={(event) => {
                            event.preventDefault();
                            const form = document.getElementById('react-logout-form') as HTMLFormElement | null;
                            form?.submit();
                        }}>
                            <i className="bx bx-log-out me-2" />
                            {t('global.logout')}
                        </DropdownItem>
                    </Dropdown>
                )}
            </div>

            <form id="react-logout-form" action={urls.logout} method="POST" className="hidden">
                <input type="hidden" name="_token" value={csrfToken} />
            </form>
        </header>
    );
}
