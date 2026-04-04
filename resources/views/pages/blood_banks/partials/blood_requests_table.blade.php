<div class="col-xl">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ $cardTitle }}</h5>
            <div class="text-muted small">
                {{ $bloodRequests->firstItem() ?? 0 }} - {{ $bloodRequests->lastItem() ?? 0 }} / {{ $bloodRequests->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ localize('global.number') }}</th>
                            <th>{{ localize('global.card_number') }}</th>
                            <th>{{ localize('global.patient_name') }}</th>
                            <th>{{ localize('global.father_name') }}</th>
                            <th>{{ localize('global.requested_department') }}</th>
                            <th>{{ localize('global.blood_group') }}</th>
                            <th>{{ localize('global.rh') }}</th>
                            <th>{{ localize('global.blood_type') }}</th>
                            <th>{{ localize('global.quantity') }}</th>
                            <th>{{ localize('global.status') }}</th>
                            <th class="text-end">{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bloodRequests as $bloodRequest)
                            <tr>
                                <td>{{ $bloodRequests->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $bloodRequest->patient?->id_card ?? '-' }}</span>
                                </td>
                                <td>{{ $bloodRequest->patient?->name ?? '—' }}</td>
                                <td>
                                    <span class="text-muted">{{ $bloodRequest->patient?->father_name ?? '-' }}</span>
                                </td>
                                <td>{{ $bloodRequest->department?->name ?? '—' }}</td>
                                <td>
                                    @if ($bloodRequest->group == 'A')
                                        <span class="text-danger"><i class="bx fa-solid fa-a"></i></span>
                                    @elseif($bloodRequest->group == 'B')
                                        <span class="text-danger"><i class="bx fa-solid fa-b"></i></span>
                                    @elseif($bloodRequest->group == 'AB')
                                        <span class="text-danger" dir="ltr"><i class="bx fa-solid fa-a"></i><i
                                                class="bx fa-solid fa-b"></i></span>
                                    @elseif($bloodRequest->group == 'O')
                                        <span class="text-danger"><i class="bx fa-solid fa-o"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if ($bloodRequest->rh == '+')
                                        <span class="bx bx-plus-circle text-danger"></span>
                                    @else
                                        <span class="bx bx-minus-circle text-danger"></span>
                                    @endif
                                </td>
                                <td>{{ $bloodRequest->type }}</td>
                                <td>{{ $bloodRequest->quantity }}</td>
                                <td>{{ $bloodRequest->status }}</td>
                                <td class="text-end">
                                    <a href="{{ route('blood_banks.show', $bloodRequest->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-expand me-1"></i>{{ localize('global.show') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    {{ localize('global.no_item_is_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $bloodRequests->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
