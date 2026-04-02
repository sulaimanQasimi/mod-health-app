<?php

namespace App\Http\Controllers;

use App\Models\ProstheticAttachment;
use App\Models\ProstheticCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProstheticAttachmentController extends Controller
{
    public function index(ProstheticCase $prosthetic_case)
    {
        $attachments = $prosthetic_case->attachments()
            ->latest('created_at')
            ->get();

        return view('pages.prosthetics.attachments.index', compact('prosthetic_case', 'attachments'));
    }

    public function upload(Request $request, ProstheticCase $prosthetic_case)
    {
        if (in_array($prosthetic_case->status, [ProstheticCase::STATUS_CLOSED, ProstheticCase::STATUS_CANCELLED], true)) {
            return back()->with('error', 'This prosthetic case is closed and attachments cannot be changed.');
        }

        $data = $request->validate([
            'category' => 'nullable|string|max:64',
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240', // 10MB
            'description' => 'nullable|string|max:500',
        ]);

        $uploaded = [];

        foreach ($request->file('files', []) as $file) {
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('prosthetics_attachments', $filename, 'public');

            $attachment = ProstheticAttachment::create([
                'attachable_type' => ProstheticCase::class,
                'attachable_id' => $prosthetic_case->id,
                'category' => $data['category'] ?? 'general',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'created_by' => Auth::id(),
            ]);

            $uploaded[] = [
                'id' => $attachment->id,
                'file_name' => $attachment->original_name,
                'file_url' => $attachment->file_url,
            ];
        }

        return back()->with('success', __('global.files_uploaded_successfully') ?? 'Files uploaded successfully');
    }

    public function delete(ProstheticAttachment $attachment)
    {
        $attachable = $attachment->attachable;
        if ($attachable instanceof ProstheticCase
            && in_array($attachable->status, [ProstheticCase::STATUS_CLOSED, ProstheticCase::STATUS_CANCELLED], true)) {
            return back()->with('error', 'This prosthetic case is closed and attachments cannot be changed.');
        }

        // Physical delete handled by model deleting hook, but we call it explicitly for safety.
        $attachment->deleteFile();
        $attachment->delete();

        return back()->with('success', __('global.file_deleted_successfully') ?? 'File deleted successfully');
    }
}

