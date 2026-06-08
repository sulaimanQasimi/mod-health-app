import { useTranslation } from '../../hooks/useTranslation';

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

interface BackArrowIconProps {
    className?: string;
}

export default function BackArrowIcon({ className = '' }: BackArrowIconProps) {
    const { direction } = useTranslation();

    return (
        <i
            className={mergeClasses(
                'bx bx-arrow-back',
                direction === 'rtl' && 'rotate-180',
                className,
            )}
        />
    );
}
