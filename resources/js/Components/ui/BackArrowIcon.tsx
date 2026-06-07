function mergeClasses(...classes: (string | false | null | undefined)[]) {
    return classes.filter(Boolean).join(' ');
}

interface BackArrowIconProps {
    className?: string;
}

export default function BackArrowIcon({ className = '' }: BackArrowIconProps) {
    return <i className={mergeClasses('bx bx-arrow-back rtl:rotate-180', className)} />;
}
