<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ config('app.url') }}">
  <title>My App</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
  <div id="app"></div>
</body>
</html>
