@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Attachments — <code>{{ $prosthetic_case->case_number }}</code></h4>
                <a href="{{ route('prosthetics.cases.show', $prosthetic_case) }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="post"
                          action="{{ route('prosthetics.cases.attachments.upload', $prosthetic_case) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small">Category</label>
                                <input type="text" name="category" class="form-control form-control-sm" value="general">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Files</label>
                                <input type="file" name="files[]" class="form-control form-control-sm" multiple required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary mt-0">Upload</button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small">Description (optional)</label>
                            <input type="text" name="description" class="form-control form-control-sm">
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attachments as $att)
                                <tr>
                                    <td style="min-width:240px">
                                        <a href="{{ $att->file_url }}" target="_blank" class="text-primary text-decoration-underline">
                                            {{ $att->original_name ?? basename($att->path) }}
                                        </a>
                                    </td>
                                    <td>{{ $att->category ?? 'general' }}</td>
                                    <td>{{ $att->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <form method="post"
                                              action="{{ route('prosthetics.attachments.delete', $att->id) }}"
                                              onsubmit="return confirm('Delete this attachment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No attachments yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

