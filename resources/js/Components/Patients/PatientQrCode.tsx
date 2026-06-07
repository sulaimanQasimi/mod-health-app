import QRCode from 'qrcode';
import { useEffect, useState } from 'react';

interface PatientQrCodeProps {
    patientId: number;
    size?: number;
    className?: string;
}

export default function PatientQrCode({ patientId, size = 112, className = '' }: PatientQrCodeProps) {
    const [dataUrl, setDataUrl] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        QRCode.toDataURL(String(patientId), {
            width: size,
            margin: 1,
            color: { dark: '#1e3a8a', light: '#ffffff' },
        })
            .then((url) => {
                if (!cancelled) {
                    setDataUrl(url);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setDataUrl(null);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [patientId, size]);

    if (!dataUrl) {
        return (
            <div
                className={`flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 ${className}`}
                style={{ width: size, height: size }}
            >
                <i className="bx bx-qr text-3xl text-gray-400" />
            </div>
        );
    }

    return (
        <img
            src={dataUrl}
            alt={`Patient ${patientId} QR`}
            width={size}
            height={size}
            className={`rounded-lg bg-white p-1 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 ${className}`}
        />
    );
}
