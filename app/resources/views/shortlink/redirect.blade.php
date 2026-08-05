<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="refresh" content="0; url={{ $url }}">
    <title>Mengalihkan…</title>
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ $gtm_id }}');
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'campaign_link_click',
            campaign_id: '{{ $campaign_id }}',
            short_code: '{{ $short_code }}'
        });
        setTimeout(function () {
            window.location.replace(@json($url));
        }, 120);
    </script>
</head>
<body>
    <p style="font-family: sans-serif;">Mengalihkan ke tujuan…</p>
</body>
</html>
