<!DOCTYPE html>
<html lang="id" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="theme-color" content="#0b1221">
        <title>Maintenance - Zonagim</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-48x48.png" type="image/png" sizes="48x48">
        <style>
            :root {
                color-scheme: dark;
                --background: #0b1221;
                --border: #22304a;
                --text: #dbe4f0;
                --muted: #94a3b8;
                --primary: #eab308;
                --primary-hover: #ca8a04;
                --primary-text: #0b1221;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }

            [data-theme="light"] {
                color-scheme: light;
                --background: #f8fafc;
                --border: #dbe3ee;
                --text: #0b1221;
                --muted: #64748b;
            }

            * {
                box-sizing: border-box;
            }

            body {
                min-height: 100vh;
                margin: 0;
                background: var(--background);
                color: var(--text);
            }

            .page {
                display: grid;
                min-height: 100vh;
                grid-template-rows: 1fr auto;
            }

            main {
                display: flex;
                width: min(100% - 2rem, 72rem);
                margin: 0 auto;
                align-items: center;
                padding: 4rem 0;
            }

            .content {
                width: 100%;
                text-align: center;
            }

            .label {
                margin: 0;
                color: var(--primary);
                font-size: clamp(4rem, 12vw, 7rem);
                font-weight: 800;
                line-height: 1;
                letter-spacing: -0.06em;
            }

            h1 {
                max-width: 48rem;
                margin: 1.5rem auto 0;
                font-size: clamp(2rem, 6vw, 4rem);
                font-weight: 800;
                line-height: 1.1;
                letter-spacing: -0.045em;
            }

            .button {
                display: inline-flex;
                min-height: 3rem;
                margin-top: 2rem;
                align-items: center;
                justify-content: center;
                gap: 0.625rem;
                border: 0;
                border-radius: 0.75rem;
                background: var(--primary);
                padding: 0.75rem 1.25rem;
                color: var(--primary-text);
                font: inherit;
                font-size: 0.875rem;
                font-weight: 800;
                cursor: pointer;
            }

            .button:hover {
                background: var(--primary-hover);
            }

            .button:focus-visible {
                outline: 2px solid var(--primary);
                outline-offset: 3px;
            }

            footer {
                width: min(100% - 2rem, 72rem);
                margin: 0 auto;
                border-top: 1px solid var(--border);
                padding: 1.5rem 0;
                color: var(--muted);
                font-size: 0.75rem;
                text-align: center;
            }

            @media (max-width: 640px) {
                .button {
                    width: 100%;
                }
            }
        </style>
        <script>
            try {
                document.documentElement.dataset.theme = localStorage.getItem('user-theme') === 'light' ? 'light' : 'dark';
            } catch (error) {}
        </script>
    </head>
    <body>
        <div class="page">
            <main>
                <section class="content" aria-labelledby="maintenance-title">
                    <p class="label">503</p>
                    <h1 id="maintenance-title">Website Sedang Dalam Perbaikan</h1>
                    <button class="button" type="button" onclick="window.location.reload()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 12a8 8 0 1 1-2.34-5.66L20 8"></path>
                            <path d="M20 4v4h-4"></path>
                        </svg>
                        Coba Lagi
                    </button>
                </section>
            </main>

            <footer>&copy; {{ date('Y') }} Zonagim</footer>
        </div>
    </body>
</html>
