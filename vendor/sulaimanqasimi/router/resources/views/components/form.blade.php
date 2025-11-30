<form 
    method="{{ $getFormMethod() }}" 
    action="{{ $action }}"
    @if($hasFiles) enctype="multipart/form-data" @endif
    @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
>
    @if($getHiddenMethodField())
        @method($getHiddenMethodField())
    @endif
    
    @csrf
    
    {{ $slot }}
</form>
