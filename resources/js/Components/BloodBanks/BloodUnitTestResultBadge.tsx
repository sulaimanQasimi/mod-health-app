import { Badge } from 'flowbite-react';
import { testResultBadgeColor } from './bloodBankUi';

interface BloodUnitTestResultBadgeProps {
    result: string | null;
}

export default function BloodUnitTestResultBadge({ result }: BloodUnitTestResultBadgeProps) {
    if (!result) {
        return <span className="text-gray-400">—</span>;
    }

    return (
        <Badge color={testResultBadgeColor(result)} className="w-fit font-normal capitalize">
            {result}
        </Badge>
    );
}
