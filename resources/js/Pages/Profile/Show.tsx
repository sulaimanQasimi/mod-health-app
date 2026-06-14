import { Head, useForm } from '@inertiajs/react';
import { Alert, Button, Card, Label, Spinner, TextInput } from 'flowbite-react';
import { ChangeEvent, FormEvent, useRef, useState } from 'react';
import DashboardLayout from '../../Components/Layout/DashboardLayout';
import { useTranslation } from '../../hooks/useTranslation';
import { UserProfile, ProfileUrls } from '../../types/user';
import { isUploadedFile, submitOptionsWithOptionalFile } from '../../utils/inertiaSubmit';

interface ProfileShowProps {
    profile: UserProfile;
    defaultAvatar: string;
    urls: ProfileUrls;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
}

export default function ProfileShow({ profile, defaultAvatar, urls, flash }: ProfileShowProps) {
    const { t } = useTranslation();
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [avatarPreview, setAvatarPreview] = useState(profile.avatar_url);

    const profileForm = useForm({
        name: profile.name,
        last_name: profile.last_name,
        email: profile.email,
        avatar: null as File | null,
    });

    const passwordForm = useForm({
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    });

    const handleAvatarChange = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        profileForm.setData('avatar', file);

        if (file) {
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                if (typeof loadEvent.target?.result === 'string') {
                    setAvatarPreview(loadEvent.target.result);
                }
            };
            reader.readAsDataURL(file);
        }
    };

    const handleProfileSubmit = (event: FormEvent) => {
        event.preventDefault();

        const hasAvatar = isUploadedFile(profileForm.data.avatar);
        const options = submitOptionsWithOptionalFile(hasAvatar);

        profileForm.transform((data) => {
            const payload = { ...data };

            if (!hasAvatar) {
                delete (payload as { avatar?: File | null }).avatar;
            }

            return payload;
        });

        profileForm.put(urls.update, options);
    };

    const handlePasswordSubmit = (event: FormEvent) => {
        event.preventDefault();

        passwordForm.put(urls.updatePassword, {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
            },
        });
    };

    const displayName = `${profile.name} ${profile.last_name}`.trim();

    return (
        <DashboardLayout>
            <Head title={t('global.my_profile')} />

            <div className="mx-auto max-w-5xl space-y-6">
                {flash?.success && (
                    <Alert color="success" className="mb-2">
                        {flash.success}
                    </Alert>
                )}

                {flash?.error && (
                    <Alert color="failure" className="mb-2">
                        {flash.error}
                    </Alert>
                )}

                <Card className="shadow-sm">
                    <div className="flex flex-col gap-6 sm:flex-row sm:items-center">
                        <img
                            src={avatarPreview || defaultAvatar}
                            alt={displayName}
                            className="h-24 w-24 rounded-full border-4 border-white object-cover shadow-md dark:border-gray-700"
                        />
                        <div className="min-w-0 flex-1">
                            <h1 className="text-2xl font-bold text-gray-900 dark:text-white">{displayName}</h1>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{profile.email}</p>
                            <div className="mt-3 flex flex-wrap gap-2">
                                {profile.roles.map((role) => (
                                    <span
                                        key={role.id}
                                        className="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        {role.name}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </Card>

                <div className="grid gap-6 lg:grid-cols-3">
                    <Card className="shadow-sm lg:col-span-1">
                        <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {t('global.about_me')}
                        </h2>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="font-medium text-gray-500 dark:text-gray-400">
                                    {t('global.full_name')}
                                </dt>
                                <dd className="mt-0.5 text-gray-900 dark:text-white">{displayName}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-gray-500 dark:text-gray-400">
                                    {t('global.email')}
                                </dt>
                                <dd className="mt-0.5 text-gray-900 dark:text-white">{profile.email}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-gray-500 dark:text-gray-400">
                                    {t('global.status')}
                                </dt>
                                <dd className="mt-0.5 text-gray-900 dark:text-white">
                                    {profile.status === 1 ? t('global.active') : t('global.deactive')}
                                </dd>
                            </div>
                            {profile.branch_name && (
                                <div>
                                    <dt className="font-medium text-gray-500 dark:text-gray-400">
                                        {t('global.branch')}
                                    </dt>
                                    <dd className="mt-0.5 text-gray-900 dark:text-white">{profile.branch_name}</dd>
                                </div>
                            )}
                            {profile.department_name && (
                                <div>
                                    <dt className="font-medium text-gray-500 dark:text-gray-400">
                                        {t('global.department')}
                                    </dt>
                                    <dd className="mt-0.5 text-gray-900 dark:text-white">{profile.department_name}</dd>
                                </div>
                            )}
                            {profile.joined_at && (
                                <div>
                                    <dt className="font-medium text-gray-500 dark:text-gray-400">
                                        {t('global.joined_at')}
                                    </dt>
                                    <dd className="mt-0.5 text-gray-900 dark:text-white">{profile.joined_at}</dd>
                                </div>
                            )}
                        </dl>
                    </Card>

                    <div className="space-y-6 lg:col-span-2">
                        <Card className="shadow-sm">
                            <h2 className="mb-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.profile_avatar')}
                            </h2>
                            <p className="mb-6 text-sm text-gray-500 dark:text-gray-400">
                                {t('global.name')}, {t('global.email')}
                            </p>

                            <form onSubmit={handleProfileSubmit} className="space-y-4">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                                    <div>
                                        <img
                                            src={avatarPreview || defaultAvatar}
                                            alt=""
                                            className="h-20 w-20 rounded-lg object-cover"
                                        />
                                    </div>
                                    <div className="flex-1">
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/gif"
                                            className="hidden"
                                            onChange={handleAvatarChange}
                                        />
                                        <Button
                                            type="button"
                                            color="light"
                                            onClick={() => fileInputRef.current?.click()}
                                        >
                                            {t('global.choose_file')}
                                        </Button>
                                        {profileForm.errors.avatar && (
                                            <p className="mt-1 text-sm text-red-600">{profileForm.errors.avatar}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label htmlFor="name">{t('global.name')}</Label>
                                        <TextInput
                                            id="name"
                                            value={profileForm.data.name}
                                            onChange={(e) => profileForm.setData('name', e.target.value)}
                                            required
                                        />
                                        {profileForm.errors.name && (
                                            <p className="mt-1 text-sm text-red-600">{profileForm.errors.name}</p>
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="last_name">{t('global.last_name')}</Label>
                                        <TextInput
                                            id="last_name"
                                            value={profileForm.data.last_name}
                                            onChange={(e) => profileForm.setData('last_name', e.target.value)}
                                            required
                                        />
                                        {profileForm.errors.last_name && (
                                            <p className="mt-1 text-sm text-red-600">{profileForm.errors.last_name}</p>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <Label htmlFor="email">{t('global.email')}</Label>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        value={profileForm.data.email}
                                        onChange={(e) => profileForm.setData('email', e.target.value)}
                                        required
                                    />
                                    {profileForm.errors.email && (
                                        <p className="mt-1 text-sm text-red-600">{profileForm.errors.email}</p>
                                    )}
                                </div>

                                <div className="flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <Button type="submit" disabled={profileForm.processing}>
                                        {profileForm.processing && <Spinner size="sm" className="me-2" />}
                                        {t('global.save')}
                                    </Button>
                                </div>
                            </form>
                        </Card>

                        <Card className="shadow-sm">
                            <h2 className="mb-6 text-lg font-semibold text-gray-900 dark:text-white">
                                {t('global.change_password')}
                            </h2>

                            <form onSubmit={handlePasswordSubmit} className="space-y-4">
                                <div>
                                    <Label htmlFor="current_password">{t('global.current_password')}</Label>
                                    <TextInput
                                        id="current_password"
                                        type="password"
                                        value={passwordForm.data.current_password}
                                        onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                        required
                                    />
                                    {passwordForm.errors.current_password && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {passwordForm.errors.current_password}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label htmlFor="new_password">{t('global.new_password')}</Label>
                                        <TextInput
                                            id="new_password"
                                            type="password"
                                            value={passwordForm.data.new_password}
                                            onChange={(e) => passwordForm.setData('new_password', e.target.value)}
                                            required
                                        />
                                        {passwordForm.errors.new_password && (
                                            <p className="mt-1 text-sm text-red-600">
                                                {passwordForm.errors.new_password}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor="new_password_confirmation">
                                            {t('global.password_confirmation')}
                                        </Label>
                                        <TextInput
                                            id="new_password_confirmation"
                                            type="password"
                                            value={passwordForm.data.new_password_confirmation}
                                            onChange={(e) =>
                                                passwordForm.setData('new_password_confirmation', e.target.value)
                                            }
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <Button
                                        type="button"
                                        color="light"
                                        onClick={() => passwordForm.reset()}
                                        disabled={passwordForm.processing}
                                    >
                                        {t('global.cancel')}
                                    </Button>
                                    <Button type="submit" disabled={passwordForm.processing}>
                                        {passwordForm.processing && <Spinner size="sm" className="me-2" />}
                                        {t('global.update_password')}
                                    </Button>
                                </div>
                            </form>
                        </Card>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}
