import { Editor } from '@tinymce/tinymce-react';
import { useState } from 'react';
import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/skins/ui/oxide/skin.min.css';
import 'tinymce/themes/silver';

export default function RichTextEditor({ name, value, defaultValue = '', onChange, height = 280, placeholder = '' }) {
    const [internalValue, setInternalValue] = useState(defaultValue);
    const content = value ?? internalValue;

    const updateContent = (nextValue) => {
        if (value === undefined) {
            setInternalValue(nextValue);
        }
        onChange?.(nextValue);
    };

    return (
        <>
            {name ? <input type="hidden" name={name} value={content} /> : null}
            <div className="overflow-hidden rounded-md border border-[#c8d4de] bg-white focus-within:ring-2 focus-within:ring-[#00559b]">
                <Editor
                    tinymce={tinymce}
                    licenseKey="gpl"
                    value={content}
                    onEditorChange={updateContent}
                    init={{
                        height,
                        menubar: false,
                        placeholder,
                        plugins: 'lists link table',
                        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | removeformat',
                        skin: false,
                        content_css: false,
                        content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; color: #0f172a; padding: 8px; }',
                        branding: false,
                        promotion: false,
                    }}
                />
            </div>
        </>
    );
}
