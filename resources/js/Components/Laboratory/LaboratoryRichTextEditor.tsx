import { useEffect, useRef } from 'react';

interface LaboratoryRichTextEditorProps {
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
}

type CkEditorInstance = {
    getData: () => string;
    setData: (data: string) => void;
    destroy: () => Promise<void>;
    isReadOnly: boolean;
    model: {
        document: {
            on: (event: string, callback: () => void) => void;
        };
    };
};

export default function LaboratoryRichTextEditor({
    value,
    onChange,
    disabled = false,
}: LaboratoryRichTextEditorProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const editorRef = useRef<CkEditorInstance | null>(null);

    useEffect(() => {
        let cancelled = false;

        import('../../ckeditor').then(() => {
            const ClassicEditor = (window as typeof window & { ClassicEditor?: unknown })
                .ClassicEditor as {
                create: (element: HTMLElement, config?: object) => Promise<CkEditorInstance>;
            };

            if (cancelled || !containerRef.current || editorRef.current || !ClassicEditor) {
                return;
            }

            ClassicEditor.create(containerRef.current, {
                toolbar: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'underline',
                    '|',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'blockQuote',
                    'insertTable',
                    '|',
                    'undo',
                    'redo',
                ],
            })
                .then((editor) => {
                    if (cancelled) {
                        editor.destroy().catch(() => undefined);
                        return;
                    }

                    editorRef.current = editor;
                    editor.setData(value);
                    editor.isReadOnly = disabled;
                    editor.model.document.on('change:data', () => {
                        onChange(editor.getData());
                    });
                })
                .catch(() => undefined);
        });

        return () => {
            cancelled = true;
            editorRef.current?.destroy().catch(() => undefined);
            editorRef.current = null;
        };
    }, []);

    useEffect(() => {
        const editor = editorRef.current;
        if (!editor) {
            return;
        }

        if (value !== editor.getData()) {
            editor.setData(value);
        }
        editor.isReadOnly = disabled;
    }, [value, disabled]);

    return (
        <div className="overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600 [&_.ck-editor__editable]:min-h-[200px]">
            <div ref={containerRef} />
        </div>
    );
}
