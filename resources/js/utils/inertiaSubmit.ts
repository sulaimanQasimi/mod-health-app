/**
 * Use multipart FormData only when uploading a file.
 * Forcing FormData on PUT without a file prevents Laravel from reading text fields.
 */
export function submitOptionsWithOptionalFile(hasFile: boolean) {
    return {
        preserveScroll: true,
        ...(hasFile ? { forceFormData: true as const } : {}),
    };
}

export function isUploadedFile(value: unknown): value is File {
    return value instanceof File;
}
