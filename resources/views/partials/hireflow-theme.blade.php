{{--
    The HireFlow visual language (public/hireflow-theme.css), for HireFlow pages
    only. Include it at the top of a page's content and wrap that content in
    <div class="hf-theme"> — the theme's Bootstrap rebinding applies only inside
    that wrapper, so the rest of zen-admin keeps its own look.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('hireflow-theme.css') }}?v={{ @filemtime(public_path('hireflow-theme.css')) ?: 1 }}">
@endpush
