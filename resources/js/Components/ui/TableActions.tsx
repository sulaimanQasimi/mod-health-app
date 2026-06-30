import { ReactNode } from 'react';
import { TableCell } from './Table';

interface TableActionsProps {
    children: ReactNode;
    className?: string;
}

export function TableActions({ children, className }: TableActionsProps) {
    return (
        <div className={className ?? 'inline-flex flex-nowrap items-center justify-center gap-0.5'}>
            {children}
        </div>
    );
}

interface TableActionsCellProps {
    children: ReactNode;
    className?: string;
}

export function TableActionsCell({ children, className }: TableActionsCellProps) {
    return (
        <TableCell align="center" className={className}>
            <TableActions>{children}</TableActions>
        </TableCell>
    );
}
