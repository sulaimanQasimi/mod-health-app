<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($labs as $lab)
            <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $lab->labType->name }}</td>
                <td>{{ $lab->patient->name }}</td>
                <td>
                    <a href="{{ route('lab_tests.edit', $lab) }}"><i class="bx bx-expand"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="col-md-12 mt-4 mb-4">
    {{$labs->links('pagination::bootstrap-4')}}
</div>
