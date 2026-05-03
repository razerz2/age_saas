<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Bloqueado</title>
    <link rel="stylesheet" href="{{ asset('tailadmin/assets/css/style.css') }}">
</head>
<body class="bg-gray-2">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-lg rounded-[10px] bg-white p-8 shadow-1">
            <h1 class="mb-2 text-2xl font-semibold text-dark">Acesso indisponível</h1>
            <p class="mb-6 text-body-color">
                {{ $message ?? 'O acesso a esta clínica está suspenso ou inativo. Entre em contato com o administrador.' }}
            </p>

            @if (! empty($tenant?->subdomain))
                <div class="rounded-md bg-gray-1 px-4 py-3 text-sm text-dark-6">
                    Clínica: <strong>{{ $tenant->subdomain }}</strong>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
