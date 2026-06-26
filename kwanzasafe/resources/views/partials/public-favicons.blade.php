{{-- Favicons completos (ficheiros em public_html/assets/images/icons/) --}}
@php $ic = asset('assets/images/icons'); @endphp
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $ic }}/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $ic }}/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $ic }}/apple-touch-icon.png">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#009d44">
