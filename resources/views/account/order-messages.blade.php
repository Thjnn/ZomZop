@if(session('success'))<p class="p-4 rounded-xl bg-green-50 text-green-700">{{ session('success') }}</p>@endif
@if($errors->any())<div role="alert" class="p-4 rounded-xl bg-red-50 text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
