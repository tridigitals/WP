import { type Editor } from '@ckeditor/ckeditor5-core';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { CKEditor as CKEditorComponent } from '@ckeditor/ckeditor5-react';
import { useEffect, useState } from 'react';

interface Props {
  value: string;
  onChange: (value: string) => void;
  error?: string;
}

interface EditorInstance extends Editor {
  getData(): string;
}

export function CKEditor({ value, onChange, error }: Props) {
  const [mounted, setMounted] = useState(false);
  const [editorInstance, setEditorInstance] = useState<EditorInstance | null>(null);

  // Wait until mounted to prevent hydration mismatch
  useEffect(() => {
    setMounted(true);
  }, []);

  const isDarkMode = document.documentElement.classList.contains('dark');

  const config = {
    toolbar: [
      'heading',
      '|',
      'bold',
      'italic',
      'link',
      'bulletedList',
      'numberedList',
      '|',
      'outdent',
      'indent',
      '|',
      'blockQuote',
      'insertTable',
      'mediaEmbed',
      'undo',
      'redo'
    ],
  };

  if (!mounted) {
    return null;
  }

  return (
    <div className={`w-full ${isDarkMode ? 'dark-editor' : 'light-editor'}`}>
      <style>
        {`
          .dark-editor .ck.ck-editor__main > .ck-editor__editable {
            background: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
          }

          .dark-editor .ck.ck-editor__main > .ck-editor__editable.ck-focused {
            border-color: #60a5fa !important;
          }

          .dark-editor .ck.ck-toolbar {
            background: #374151 !important;
            border-color: #374151 !important;
          }

          .dark-editor .ck.ck-toolbar .ck-toolbar__items button {
            color: #e5e7eb !important;
          }

          .dark-editor .ck.ck-toolbar .ck-toolbar__items button:hover {
            background: #4b5563 !important;
          }

          .dark-editor .ck.ck-toolbar .ck-toolbar__items button.ck-on {
            background: #4b5563 !important;
            color: #60a5fa !important;
          }

          .dark-editor .ck.ck-button.ck-on,
          .dark-editor a.ck.ck-button.ck-on {
            background: #4b5563 !important;
            color: #60a5fa !important;
          }

          .dark-editor .ck.ck-list__item .ck-button:hover:not(.ck-disabled) {
            background: #4b5563 !important;
          }

          .dark-editor .ck-dropdown__panel,
          .dark-editor .ck.ck-dropdown.ck-heading-dropdown .ck-dropdown__panel {
            background: #1f2937 !important;
            border-color: #374151 !important;
          }

          .dark-editor .ck.ck-list {
            background: #1f2937 !important;
          }

          .dark-editor .ck.ck-list__item .ck-button {
            color: #e5e7eb !important;
          }

          .dark-editor .ck.ck-list__item .ck-button.ck-on {
            background: #4b5563 !important;
            color: #60a5fa !important;
          }

          .dark-editor .ck.ck-toolbar .ck.ck-toolbar__separator {
            background: #4b5563 !important;
          }

          .dark-editor .ck-content .table {
            border-color: #374151 !important;
          }

          .dark-editor .ck-content .table table {
            border-color: #374151 !important;
          }

          .dark-editor .ck-content .table table td,
          .dark-editor .ck-content .table table th {
            border-color: #374151 !important;
            background: #1f2937 !important;
            color: #e5e7eb !important;
          }

          .dark-editor .ck.ck-link-form,
          .dark-editor .ck.ck-link-actions {
            background: #1f2937 !important;
            border-color: #374151 !important;
          }

          .dark-editor .ck.ck-link-form .ck-labeled-field-view {
            background: #374151 !important;
            border-color: #4b5563 !important;
          }

          .dark-editor .ck.ck-link-form .ck-labeled-field-view .ck-input {
            color: #e5e7eb !important;
          }

          .dark-editor .ck.ck-link-form .ck-button,
          .dark-editor .ck.ck-link-actions .ck-button {
            color: #e5e7eb !important;
          }

          .dark-editor .ck.ck-link-form .ck-button:hover,
          .dark-editor .ck.ck-link-actions .ck-button:hover {
            background: #4b5563 !important;
          }

          .dark-editor .ck.ck-reset_all * {
            color: #e5e7eb !important;
          }
          
          .ck.ck-editor {
            width: 100%;
          }

          .light-editor .ck.ck-editor__main > .ck-editor__editable {
            background: #ffffff !important;
            color: #000000 !important;
          }
        `}
      </style>
      <CKEditorComponent
        editor={ClassicEditor}
        config={config}
        onReady={(editor) => {
          // @ts-ignore - CKEditor types are incomplete
          setEditorInstance(editor);
          // Set initial data
          editor.setData(value);
        }}
        onChange={(_event, editor) => {
          // @ts-ignore - CKEditor types are incomplete
          const data = editor.getData();
          onChange(data);
        }}
        onBlur={(_event, editor) => {
          // @ts-ignore - CKEditor types are incomplete
          const data = editor.getData();
          onChange(data);
        }}
        // @ts-ignore - CKEditor types are incomplete
        initialData={value}
      />
      {error && <span className="text-sm text-red-500">{error}</span>}
    </div>
  );
}