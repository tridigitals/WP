{{-- Basic Meta Tags --}}
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@if($keywords)
<meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph Meta Tags --}}
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif

{{-- Twitter Card Meta Tags --}}
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
@if($twitterImage)
<meta name="twitter:image" content="{{ $twitterImage }}">
@endif

{{-- Schema.org JSON-LD --}}
@if($schemaMarkup)
<script type="application/ld+json">
    {!! json_encode($schemaMarkup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif