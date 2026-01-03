<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="font-sans text-gray-900 antialiased w-screen min-h-dvh bg-gray-100 dark:bg-gray-900">
    <!-- Wrapper fullscreen -->
    <div class="w-screen min-h-dvh">
      <!-- Logo (optional) -->
      <div class="absolute top-6 left-6">
        <a href="/" wire:navigate>
          <x-application-logo class="w-12 h-12 fill-current text-gray-500" />
        </a>
      </div>

      <!-- Slot: biarkan view /apply yang ngatur layout 2 kolomnya -->
      <div class="w-full min-h-dvh">
        {{ $slot }}
      </div>
    </div>
  </body>
</html>
