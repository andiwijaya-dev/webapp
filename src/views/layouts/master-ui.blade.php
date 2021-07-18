<!DOCTYPE html>
<html>
<head>
  <title>{{ $page->title ?? env('APP_NAME') }}</title>

  <meta name="viewport" content="width=device-width, user-scalable=no" />
  <meta http-equiv=”Content-Type” content=”text/html;charset=UTF-8″>
  <meta name="description" content="{!! ($page->meta_description ?? '') !!}">
  <meta name="keywords" content="{!! $page->meta_keywords ?? '' !!}">
  @if(isset($page->canonical_url))<link rel="canonical" href="{!! $page->canonical_url !!}" />@endif
  @if(($page->no_index ?? false))<meta name="robots" content="noindex">@endif
  @if(isset($page->og_url))<meta property="og:url" content="{{ $page->og_url }}" />@endif
  @if(isset($page->og_type))<meta property="og:type" content="{{ $page->og_type ?? '' }}" />@endif
  @if(isset($page->og_title))<meta property="og:title" content="{{ $page->og_title ?? '' }}" />@endif
  @if(isset($page->og_description))<meta property="og:description"   content="{{ $page->og_description ?? '' }}" />@endif
  @if(isset($page->og_image))<meta property="og:image" itemprop="image" content="{{ $page->og_image ?? '' }}" />@endif

  <link rel="stylesheet" media="print" href="/css/ui.min.css?v={{ assets_version() }}"/>
  <link rel="stylesheet" media="print" href="/css/all.min.css?v={{ assets_version() }}"/>
  <style type="text/css" id="pre-style">
    .splash{ position: fixed; left:50%; top:50%; transform: translate3d(-50%, -50%, 0);display: none; }
    .screen{ visibility: hidden; }
  </style>
  <script type="text/javascript" src="/js/ui.min.js?v={{ assets_version() }}" defer></script>
  @stack('head')

</head>
<body class="{{ $body['class'] ?? '' }}">

  @stack('body-pre')

  <div class="splash">
  @if(!isset($no_splash) || !$no_splash)
    @yield('splash')
  @endif
  </div>

  <div class="screen">
    @yield('screen')
  </div>

</body>
</html>