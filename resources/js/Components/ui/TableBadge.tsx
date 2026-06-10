import { Badge, BadgeProps } from 'flowbite-react';

export default function TableBadge({ className = '', ...props }: BadgeProps) {
    return (
        <Badge
            size="xs"
            className={`inline-flex w-fit font-normal whitespace-nowrap ${className}`.trim()}
            {...props}
        />
    );
}
