<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- NIS: Apply saved appearance before paint — prevents flash on refresh --}}
        <script>
            (function () {
                try {
                    var theme    = localStorage.getItem('nis-theme');
                    var fontSize = localStorage.getItem('nis-font-size');
                    var accent   = localStorage.getItem('nis-accent');
                    var root     = document.documentElement;

                    // ── Theme ────────────────────────────────────────────────
                    if (theme === 'nis-dark') {
                        root.classList.remove('nis-light');
                        root.classList.add('dark');
                    } else if (theme === 'nis-light') {
                        root.classList.remove('dark');
                        root.classList.add('nis-light');
                    } else {
                        // 'system' or first visit — follow device preference
                        root.classList.remove('nis-light');
                        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            root.classList.add('dark');
                        }
                    }

                    // ── Font size ────────────────────────────────────────────
                    var fontMap = { sm: '14px', md: '16px', lg: '18px' };
                    if (fontSize && fontMap[fontSize]) {
                        root.style.fontSize = fontMap[fontSize];
                    }

                    // ── Accent color ─────────────────────────────────────────
                    var accentMap = {
                        green: { primary: 'hsl(137 56% 23%)', primaryFg: 'hsl(0 0% 98%)', ring: 'hsl(137 56% 23%)' },
                        gold:  { primary: 'hsl(43 72% 47%)',  primaryFg: 'hsl(0 0% 9%)',  ring: 'hsl(43 72% 47%)'  },
                        blue:  { primary: 'hsl(207 69% 37%)', primaryFg: 'hsl(0 0% 98%)', ring: 'hsl(207 69% 37%)' },
                        rice:  { primary: 'hsl(83 46% 50%)',  primaryFg: 'hsl(0 0% 9%)',  ring: 'hsl(83 46% 50%)'  },
                        brown: { primary: 'hsl(25 46% 32%)',  primaryFg: 'hsl(0 0% 98%)', ring: 'hsl(25 46% 32%)'  },
                    };
                    if (accent && accentMap[accent]) {
                        var a = accentMap[accent];
                        root.style.setProperty('--primary',                    a.primary);
                        root.style.setProperty('--primary-foreground',         a.primaryFg);
                        root.style.setProperty('--ring',                       a.ring);
                        root.style.setProperty('--sidebar-primary',            a.primary);
                        root.style.setProperty('--sidebar-primary-foreground', a.primaryFg);
                        root.style.setProperty('--sidebar-ring',               a.ring);
                    }
                } catch (e) {}
            })();
        </script>

        {{-- Background set before CSS loads to prevent flash --}}
        <style>
            html                { background-color: oklch(1 0 0); }
            html.dark           { background-color: oklch(0.145 0 0); }
            html.nis-light      { background-color: hsl(138 40% 97%); }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
