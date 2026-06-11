<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documentationTitle }}</title>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        html, body { margin: 0; height: 100%; }
        rapi-doc { width: 100%; height: 100vh; }

        /* Branding inside the sidebar */
        .doxie-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 16px;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
        }
        .doxie-brand .logo {
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 10px;
            font-size: 20px;
        }
        .doxie-brand .title { font: 700 16px 'Inter', sans-serif; line-height: 1.1; }
        .doxie-brand .subtitle { font: 400 11px 'Inter', sans-serif; opacity: 0.9; margin-top: 2px; }
    </style>
</head>
<body>
    @php($specUrl = collect($urlsToDocs)->first())

    <rapi-doc
        spec-url="{{ $specUrl }}"
        render-style="focused"
        layout="row"
        schema-style="table"
        default-schema-tab="example"
        font-size="default"
        regular-font="'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"

        show-header="false"
        show-info="true"
        allow-search="true"
        allow-advanced-search="true"
        allow-try="true"
        allow-server-selection="true"
        allow-authentication="true"
        persist-auth="{{ config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' }}"

        show-method-in-nav-bar="as-colored-block"
        use-path-in-nav-bar="false"
        nav-item-spacing="relaxed"

        theme="light"
        bg-color="#ffffff"
        text-color="#1f2937"
        primary-color="#4f46e5"
        nav-bg-color="#1f2430"
        nav-text-color="#c5c8d3"
        nav-hover-bg-color="#2d3340"
        nav-hover-text-color="#ffffff"
        nav-accent-color="#06b6d4"
        header-color="#4f46e5"
    >
        <div slot="nav-logo" class="doxie-brand">
            <div class="logo">📄</div>
            <div>
                <div class="title">Doxie AI</div>
                <div class="subtitle">AI Document Q&amp;A API</div>
            </div>
        </div>
    </rapi-doc>

    <script type="module" src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
</body>
</html>
