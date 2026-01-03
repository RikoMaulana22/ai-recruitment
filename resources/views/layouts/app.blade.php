<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Recruitment AI' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <!-- penting: hilangkan bg-gray-100 kalau kamu mau halaman apply yang atur bg sendiri -->
  <body class="min-h-dvh w-screen font-sans antialiased">
    {{ $slot }}
  </body>
</html>
