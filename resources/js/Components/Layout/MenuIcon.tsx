interface MenuIconProps {
    icon: string | null;
    className?: string;
}

export default function MenuIcon({ icon, className = 'text-lg' }: MenuIconProps) {
    if (!icon) {
        return null;
    }

    if (icon === 'dentist') {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`h-5 w-5 ${className}`}>
                <path d="M19.03 2.13a4.75 4.75 0 0 0-5.54 1.55l-.68.91c-.38.5-1.23.5-1.6 0l-.68-.91a4.72 4.72 0 0 0-5.54-1.55 4.7 4.7 0 0 0-2.97 4.39c0 1.72.21 3.44.63 5.12l2.04 8.18A2.88 2.88 0 0 0 7.48 22h.12c1.28 0 2.41-.86 2.76-2.08l1.2-4.2c.12-.42.51-.72.95-.72s.83.29.95.72l1.2 4.2A2.88 2.88 0 0 0 17.42 22c1.41 0 2.6-1.01 2.83-2.4l1.33-7.99c.28-1.67.42-3.38.42-5.08 0-1.95-1.17-3.67-2.97-4.39Z" />
            </svg>
        );
    }

    if (icon === 'nephrology') {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className={`h-5 w-5 ${className}`}>
                <path d="M8 22h8c1.1 0 2-.9 2-2V8c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2v12c0 1.1.9 2 2 2M6 4h12v2H6zm10 4v3h-5v6h5v3H8V8z" />
            </svg>
        );
    }

    return <i className={`bx ${icon} ${className}`} />;
}
