<!DOCTYPE html>
<html>
<head>
  <title>{{ $page['title'] ?? env('APP_NAME') }}</title>

  <meta name="viewport" content="width=device-width, user-scalable=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv=”Content-Type” content=”text/html;charset=UTF-8″>
  <meta name="description" content="{!! ($page['description'] ?? '') !!}">
  <meta name="keywords" content="{!! $page['keywords'] ?? '' !!}">
  @if(isset($page['canonical']))<link rel="canonical" href="{!! $page['canonical'] !!}" />@endif
  @if(isset($page['google_client_id']))<meta name="google-signin-client_id" content="{{ $page['google_client_id'] }}">@endif
  @if(($page['noindex'] ?? false))<meta name="robots" content="noindex">@endif
  @if(isset($page['og']['url']))
    <meta property="og:url"           content="{{ $page['og']['url'] }}" />
    <meta property="og:type"          content="{{ $page['og']['type'] ?? '' }}" />
    <meta property="og:title"         content="{{ $page['og']['title'] ?? '' }}" />
    <meta property="og:description"   content="{{ $page['og']['description'] ?? '' }}" />
    <meta property="og:image"         content="{{ $page['og']['image'] ?? '' }}" />
  @endif

  <style type="text/css" id="pre-style">
    .splash{ position: fixed; left:50%; top:50%; transform: translate3d(-50%, -50%, 0);display: none; }
    .screen{ visibility: hidden; }
  </style>
  <link rel="stylesheet" media="print" href="/css/ui.min.css?v={{ assets_version() }}"/>
  <link rel="stylesheet" media="print" href="/css/all.min.css?v={{ assets_version() }}"/>
  <script type="text/javascript" src="/js/ui.min.js?v={{ assets_version() }}" defer></script>

  @stack('head')
</head>
<body>

  <div class="screen">
    @if(!isset($page['no_splash']))
    @yield('splash')
    @endif

    @yield('screen')
  </div>

</body>
</html>