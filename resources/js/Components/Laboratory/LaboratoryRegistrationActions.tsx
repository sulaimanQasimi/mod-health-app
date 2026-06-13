import { router } from '@inertiajs/react';
import {
    AppointmentActionGroup,
    AppointmentIconAnchor,
    AppointmentIconButton,
    AppointmentPillButton,
} from '../Appointments/AppointmentTableActions';
import { LaboratoryRegistrationItem } from '../../types/laboratory';
import { useTranslation } from '../../hooks/useTranslation';

interface LaboratoryRegistrationActionsProps {
    registration: LaboratoryRegistrationItem;
    listMode?: 'pending' | 'in_progress' | 'completed';
}

export default function LaboratoryRegistrationActions({
    registration,
    listMode,
}: LaboratoryRegistrationActionsProps) {
    const { t } = useTranslation();
    const { permissions, urls } = registration;

    const postAction = (url: string, confirmMessage: string) => {
        if (!window.confirm(confirmMessage)) {
            return;
        }

        router.post(url, {}, { preserveScroll: true });
    };

    const pendingOnly = listMode === 'pending';

    return (
        <AppointmentActionGroup>
            {permissions.accept && (
                <AppointmentPillButton
                    icon="bx-check"
                    label={t('global.accept')}
                    variant="accept"
                    onClick={() => postAction(urls.accept, t('global.are_you_sure') || 'Are you sure?')}
                />
            )}
            {!pendingOnly && permissions.enterResults && (
                <AppointmentIconAnchor
                    href={urls.enterResults}
                    icon="bx-edit-alt"
                    title={t('global.enter_results') || t('global.test_results')}
                    variant="edit"
                />
            )}
            {!pendingOnly && permissions.markCompleted && (
                <AppointmentPillButton
                    icon="bx-check-double"
                    label={t('global.completed')}
                    variant="accept"
                    onClick={() =>
                        postAction(
                            urls.markCompleted,
                            t('global.are_you_sure') || 'Are you sure?',
                        )
                    }
                />
            )}
            {!pendingOnly && permissions.print && (
                <AppointmentIconAnchor
                    href={urls.print}
                    icon="bx-printer"
                    title={t('global.print')}
                    variant="view"
                />
            )}
            {!pendingOnly && permissions.cancel && (
                <AppointmentIconButton
                    icon="bx-x"
                    title={t('global.cancel')}
                    variant="delete"
                    onClick={() =>
                        postAction(urls.cancel, t('global.are_you_sure') || 'Are you sure?')
                    }
                />
            )}
        </AppointmentActionGroup>
    );
}
