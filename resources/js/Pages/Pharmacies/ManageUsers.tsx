import { Head, Link, router, useForm } from '@inertiajs/react';
import { Badge, Button, Card, Label, Spinner } from 'flowbite-react';
import { FormEvent, useState } from 'react';
import SettingsPageHeader from '../../Components/Settings/SettingsPageHeader';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import SearchableSelect from '../../Components/ui/SearchableSelect';
import { useTranslation } from '../../hooks/useTranslation';
import { SETTINGS_WIDE_FORM_WIDTH } from '../../utils/settingsUi';

interface PharmacyUserRow {
    id: number;
    full_name: string;
    email: string;
    role: string;
    joined_at: string | null;
}

export default function ManagePharmacyUsers({
    pharmacy,
    users,
    availableUsers,
    roles,
    urls,
}: {
    pharmacy: { id: number; name: string };
    users: PharmacyUserRow[];
    availableUsers: { id: number; full_name: string; email: string }[];
    roles: string[];
    urls: {
        show: string;
        index: string;
        addUser: string;
        removeUser: string;
        updateUser: string;
    };
}) {
    const { t } = useTranslation();
    const [updatingUserId, setUpdatingUserId] = useState<number | null>(null);
    const [removingUserId, setRemovingUserId] = useState<number | null>(null);

    const addForm = useForm({
        user_id: '',
        role: 'staff',
    });

    const roleLabel = (role: string) => {
        if (role === 'manager') return t('global.manager');
        if (role === 'staff') return t('global.staff');
        if (role === 'procurement') return t('global.procurement');
        if (role === 'viewer') return t('global.viewer');
        return role;
    };

    const roleColor = (role: string) => {
        if (role === 'manager') return 'failure';
        if (role === 'staff') return 'info';
        if (role === 'procurement') return 'warning';
        return 'gray';
    };

    const handleAddUser = (event: FormEvent) => {
        event.preventDefault();
        addForm.post(urls.addUser, {
            preserveScroll: true,
            onSuccess: () => addForm.reset(),
        });
    };

    const handleUpdateRole = (userId: number, role: string) => {
        setUpdatingUserId(userId);
        router.put(`${urls.updateUser}/${userId}`, { role }, {
            preserveScroll: true,
            onFinish: () => setUpdatingUserId(null),
        });
    };

    const handleRemoveUser = (userId: number) => {
        if (!window.confirm(t('global.are_you_sure'))) return;
        setRemovingUserId(userId);
        router.post(
            urls.removeUser,
            { user_id: userId },
            {
                preserveScroll: true,
                onFinish: () => setRemovingUserId(null),
            },
        );
    };

    return (
        <DashboardLayout>
            <Head title={t('global.manage_users')} />
            <div className={`mx-auto ${SETTINGS_WIDE_FORM_WIDTH} space-y-6`}>
                <Card className="shadow-sm">
                    <SettingsPageHeader
                        title={t('global.manage_users')}
                        subtitle={pharmacy.name}
                        icon="bx-user-plus"
                        accent="from-emerald-500 to-teal-600"
                        backHref={urls.show}
                        backLabel={t('global.back')}
                    />

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="lg:col-span-2">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.pharmacy_users')}
                            </h2>
                            {users.length > 0 ? (
                                <div className="space-y-3">
                                    {users.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex flex-col gap-3 rounded-xl border border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700"
                                        >
                                            <div className="min-w-0 flex-1">
                                                <p className="font-medium text-gray-900 dark:text-white">
                                                    {user.full_name}
                                                </p>
                                                <p className="text-sm text-gray-500">{user.email}</p>
                                                <Badge color={roleColor(user.role)} className="mt-2">
                                                    {roleLabel(user.role)}
                                                </Badge>
                                            </div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <div className="min-w-[140px]">
                                                    <SearchableSelect
                                                        value={user.role}
                                                        onChange={(value) => handleUpdateRole(user.id, value)}
                                                        disabled={updatingUserId === user.id}
                                                    >
                                                        {roles.map((role) => (
                                                            <option key={role} value={role}>
                                                                {roleLabel(role)}
                                                            </option>
                                                        ))}
                                                    </SearchableSelect>
                                                </div>
                                                <Button
                                                    color="light"
                                                    disabled={removingUserId === user.id}
                                                    onClick={() => handleRemoveUser(user.id)}
                                                >
                                                    {removingUserId === user.id ? (
                                                        <Spinner size="sm" />
                                                    ) : (
                                                        <i className="bx bx-trash text-lg text-red-600" />
                                                    )}
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500">{t('global.no_users_assigned')}</p>
                            )}
                        </div>

                        <div>
                            <Card className="shadow-sm">
                                <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">
                                    {t('global.add_user')}
                                </h3>
                                <form onSubmit={handleAddUser} className="space-y-4">
                                    <div>
                                        <Label>{t('global.select_user')}</Label>
                                        <SearchableSelect
                                            value={addForm.data.user_id}
                                            onChange={(value) => addForm.setData('user_id', value)}
                                            options={availableUsers.map((user) => ({
                                                value: String(user.id),
                                                label: `${user.full_name} (${user.email})`,
                                            }))}
                                            placeholder={t('global.select_user')}
                                        />
                                        {addForm.errors.user_id && (
                                            <p className="mt-1 text-sm text-red-600">{addForm.errors.user_id}</p>
                                        )}
                                    </div>
                                    <div>
                                        <Label>{t('global.role')}</Label>
                                        <SearchableSelect
                                            value={addForm.data.role}
                                            onChange={(value) => addForm.setData('role', value)}
                                        >
                                            {roles.map((role) => (
                                                <option key={role} value={role}>
                                                    {roleLabel(role)}
                                                </option>
                                            ))}
                                        </SearchableSelect>
                                        {addForm.errors.role && (
                                            <p className="mt-1 text-sm text-red-600">{addForm.errors.role}</p>
                                        )}
                                    </div>
                                    <Button
                                        type="submit"
                                        color="blue"
                                        className="w-full"
                                        disabled={addForm.processing || !addForm.data.user_id}
                                    >
                                        {addForm.processing ? <Spinner size="sm" /> : t('global.add_user')}
                                    </Button>
                                </form>
                            </Card>
                            <Button color="light" as={Link} href={urls.index} className="mt-4 w-full">
                                {t('global.pharmacies')}
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
        </DashboardLayout>
    );
}
