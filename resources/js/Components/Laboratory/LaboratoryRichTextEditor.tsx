import { CKEditor } from '@ckeditor/ckeditor5-react';
import { useMemo } from 'react';
import {
    ClassicEditor,
    laboratoryCkEditorConfig,
} from '../../lib/laboratoryCkEditorConfig';
import 'ckeditor5/ckeditor5.css';

interface LaboratoryRichTextEditorProps {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
}

export default function LaboratoryRichTextEditor({
    value,
    onChange,
    disabled = false,
}: LaboratoryRichTextEditorProps) {
    const config = useMemo(() => ({ ...laboratoryCkEditorConfig }), []);

    return (
        <div className="laboratory-ck-editor overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600">
            <CKEditor
                editor={ClassicEditor}
                config={config}
                data={value}
                disabled={disabled}
                onChange={(_event, editor) => {
                    onChange(editor.getData());
                }}
            />
        </div>
    );
}
