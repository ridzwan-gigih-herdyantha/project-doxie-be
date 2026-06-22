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

    {{-- Realtime push (Reverb) toast demo --}}
    <style>
        #rt-toast-wrap {
            position: fixed; top: 16px; right: 16px; z-index: 99999;
            display: flex; flex-direction: column; gap: 10px; max-width: 360px;
        }
        .rt-toast {
            font: 500 13px 'Inter', sans-serif; color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            border-radius: 10px; padding: 12px 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            opacity: 0; transform: translateX(20px);
            transition: opacity .25s ease, transform .25s ease;
        }
        .rt-toast.show { opacity: 1; transform: translateX(0); }
        .rt-toast.warn { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); }
        .rt-toast .t-title { font-weight: 700; margin-bottom: 2px; }
        .rt-toast .t-body { opacity: .95; font-weight: 400; }
        #rt-status {
            position: fixed; bottom: 12px; right: 12px; z-index: 99999;
            font: 500 11px 'Inter', sans-serif; color: #6b7280;
            background: #fff; border: 1px solid #e5e7eb; border-radius: 999px;
            padding: 4px 10px; display: flex; align-items: center; gap: 6px;
        }
        #rt-status .dot { width: 8px; height: 8px; border-radius: 50%; background: #9ca3af; }
        #rt-status.on .dot { background: #10b981; }
    </style>
    <div id="rt-toast-wrap"></div>
    <div id="rt-status"><span class="dot"></span><span class="txt">Realtime: idle (authorize & hit an endpoint)</span></div>

    <script src="https://js.pusher.com/8.4/pusher.min.js"></script>
    <script>
        (function () {
            const CONFIG = {
                key: @json(config('broadcasting.connections.reverb.key')),
                host: @json(config('broadcasting.connections.reverb.options.host')),
                port: @json((int) config('broadcasting.connections.reverb.options.port')),
                scheme: @json(config('broadcasting.connections.reverb.options.scheme')),
            };

            function toast(title, body, warn) {
                const wrap = document.getElementById('rt-toast-wrap');
                const el = document.createElement('div');
                el.className = 'rt-toast' + (warn ? ' warn' : '');
                el.innerHTML = '<div class="t-title">' + title + '</div><div class="t-body">' + body + '</div>';
                wrap.appendChild(el);
                requestAnimationFrame(() => el.classList.add('show'));
                setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 300); }, 6000);
            }

            function setStatus(text, on) {
                const s = document.getElementById('rt-status');
                s.querySelector('.txt').textContent = 'Realtime: ' + text;
                s.classList.toggle('on', !!on);
            }

            let started = false;

            async function start(token) {
                if (started) { return; }
                started = true;
                setStatus('connecting…');

                let uuid;
                try {
                    const res = await fetch('/api/auth/user', {
                        headers: { Authorization: token, Accept: 'application/json' },
                    });
                    const json = await res.json();
                    uuid = (json.data && json.data.uuid) || json.uuid;
                } catch (e) {
                    started = false;
                    setStatus('failed to load user');
                    return;
                }
                if (!uuid) { started = false; setStatus('no user uuid'); return; }

                const pusher = new Pusher(CONFIG.key, {
                    wsHost: CONFIG.host,
                    wsPort: CONFIG.port,
                    wssPort: CONFIG.port,
                    forceTLS: CONFIG.scheme === 'https',
                    enabledTransports: ['ws', 'wss'],
                    disableStats: true,
                    cluster: 'mt1',
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { Authorization: token, Accept: 'application/json' } },
                });

                pusher.connection.bind('connected', () => setStatus('connected', true));
                pusher.connection.bind('error', () => setStatus('connection error'));

                const channel = pusher.subscribe('private-user.' + uuid);
                channel.bind('pusher:subscription_succeeded', () => {
                    setStatus('listening (user.' + uuid.slice(0, 8) + '…)', true);
                    toast('Realtime aktif', 'Menunggu event document.ready…');
                });
                channel.bind('pusher:subscription_error', (e) => {
                    setStatus('subscribe denied (' + (e && e.status) + ')');
                    toast('Subscribe ditolak', 'Auth gagal (status ' + (e && e.status) + ')', true);
                });
                channel.bind('document.ready', (data) => {
                    toast('📄 Dokumen siap', (data.title || 'Untitled') + ' — ' + (data.status || 'ready'));
                });
            }

            // Capture the Bearer token the moment any "Try" request is sent from RapiDoc.
            const rapidoc = document.querySelector('rapi-doc');
            if (rapidoc) {
                rapidoc.addEventListener('before-try', (e) => {
                    try {
                        const auth = e.detail.request.headers.get('Authorization');
                        if (auth && auth.toLowerCase().startsWith('bearer')) { start(auth); }
                    } catch (_) {}
                });
            }
        })();
    </script>
</body>
</html>
