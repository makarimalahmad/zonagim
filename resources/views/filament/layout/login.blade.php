<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - LapakAkunID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Filament styles dimuat lebih dulu agar override di bawah selalu menang --}}
    @filamentStyles

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: #0b1221;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }
        .login-card {
            background-color: #0e1629;
            border: 1px solid #1b2740;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border-radius: 1.5rem;
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            margin: 1.5rem;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
            color: #eab308;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        
        /* FILAMENT FORM OVERRIDES */
        .fi-simple-main { height: auto !important; }
        .fi-simple-page { margin: 0 !important; }
        .fi-section { background: transparent !important; box-shadow: none !important; ring: 0 !important; }
        .fi-header { text-align: center; margin-bottom: 2rem; }
        .fi-header-heading { color: white !important; font-size: 1.5rem !important; }
        .fi-header-subheading { color: rgba(255,255,255,0.6) !important; }
        
        /* Input overrides */
        input, .fi-input, .fi-input-wrp {
            background-color: #0b1221 !important;
            border-color: #1b2740 !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important;
        }
        input:focus, .fi-input-wrp:focus-within {
            border-color: #eab308 !important; 
            box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.2) !important;
        }
        
        /* Button overrides */
        .fi-btn-primary {
            background: #eab308 !important;
            border: none !important;
            color: black !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .fi-btn-primary:hover {
            background: #ca8a04 !important;
        }
        
        /* Labels */
        label, .fi-input-label { color: rgba(255,255,255,0.8) !important; }
        
        /* Checkbox */
        input[type="checkbox"] {
            background-color: rgba(255,255,255,0.1) !important;
            border-color: rgba(255,255,255,0.2) !important;
            border-radius: 0.25rem !important;
        }
        input[type="checkbox"]:checked {
            background-color: #eab308 !important;
            border-color: #eab308 !important;
        }
        
        /* Hide unwanted elements */
        .fi-theme-switcher { display: none !important; }
    </style>
</head>
<body class="antialiased">
    <div class="login-card">
        <div class="logo">LapakAkunID</div>
        
        {{ $slot }}
    </div>

    @filamentScripts
</body>
</html>
