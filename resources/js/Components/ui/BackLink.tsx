import { Link } from '@inertiajs/react';
import { Button } from 'flowbite-react';
import { ReactNode } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import BackArrowIcon from './BackArrowIcon';

function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

interface BackLinkProps {
    href: string;
    children: ReactNode;
    className?: string;
}

export default function BackLink({ href, children, className }: BackLinkProps) {
    const { direction } = useTranslation();

    return (
        <Button
            color="light"
            as={Link}
            href={href}
            className={mergeClasses(
                'w-fit shrink-0 gap-2',
                direction === 'rtl' && 'flex-row-reverse',
                className,
            )}
        >
            <BackArrowIcon className="shrink-0 text-lg" />
            <span>{children}</span>
        </Button>
    );
}
