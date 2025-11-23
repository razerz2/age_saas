<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor — AgeClin</title>

    {{-- CSS Freedash --}}
    <link rel="stylesheet" href="{{ asset('freedash/assets/extra-libs/c3/c3.min.css') }}">
    <link rel="stylesheet" href="{{ asset('freedash/assets/libs/chartist/dist/chartist.min.css') }}">
    <link rel="stylesheet" href="{{ asset('freedash/assets/extra-libs/jvector/jquery-jvectormap-2.0.2.css') }}">
    <link rel="stylesheet" href="{{ asset('freedash/dist/css/style.min.css') }}">

    <style>
        body {
            background: #f5f7fb !important;
            overflow: hidden;
            padding: 40px;
        }

        .monitor-card {
            height: 38vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }

        .monitor-value {
            font-size: 4.2rem;
            font-weight: 700;
            color: #2f323e;
        }

        /* Pulse quando atualiza */
        .pulse {
            transform: scale(1.02);
            box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.08);
        }

        /* Flash verde/vermelho */
        .flash-up {
            border: 2px solid #22c55e !important;
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.12);
        }

        .flash-down {
            border: 2px solid #ef4444 !important;
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0.12);
        }

        /* Modal */
        #audioModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        #audioModal .box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        /* Botões */
        .kiosk-actions {
            position: fixed;
            right: 28px;
            bottom: 22px;
            display: flex;
            gap: 10px;
            z-index: 9999;
        }
    </style>
</head>

<body>

    {{-- Modal para liberar som --}}
    <div id="audioModal">
        <div class="box">
            <h3 class="mb-3">🔊 Habilitar som</h3>
            <p class="mb-4">Clique no botão abaixo para permitir alertas sonoros neste monitor.</p>
            <button onclick="unlockAudio()" class="btn btn-primary btn-lg">
                Habilitar Som
            </button>
        </div>
    </div>

    <div class="container-fluid">

        <div class="row">
            {{-- Total de Clientes --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm monitor-card" id="card-clientes">
                    <div>
                        <h4 class="text-muted">Total de Clientes</h4>
                        <div id="clientes" class="monitor-value">0</div>
                    </div>
                </div>
            </div>

            {{-- Total de Assinaturas --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm monitor-card" id="card-assinaturas">
                    <div>
                        <h4 class="text-muted">Total de Assinaturas</h4>
                        <div id="assinaturas" class="monitor-value">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            {{-- Faturamento --}}
            <div class="col-12">
                <div class="card shadow-sm monitor-card" id="card-faturamento" style="height: 40vh;">
                    <div>
                        <h3 class="text-muted mb-2">Faturamento</h3>
                        <div class="monitor-value">R$ <span id="faturamento">0,00</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ações fixas --}}
    <div class="kiosk-actions">
        <button class="btn btn-secondary btn-lg" onclick="toggleFullscreen()">
            🖥️ Tela Cheia
        </button>

        <button class="btn btn-primary btn-lg" onclick="playTestSound()">
            🔊 Testar Som (aumento)
        </button>

        <button class="btn btn-danger btn-lg" onclick="playTestSoundDown()">
            🔉 Testar Som (queda)
        </button>
    </div>


    {{-- Áudios locais --}}
    <audio id="soundUp" preload="auto">
        <source src="{{ asset('freedash/assets/sounds/notify-uialert.mp3') }}" type="audio/mp3">
    </audio>

    <audio id="soundDown" preload="auto">
        <source src="{{ asset('freedash/assets/sounds/notify-bubble.mp3') }}" type="audio/mp3">
    </audio>

    {{-- JS Freedash básico --}}
    <script src="{{ asset('freedash/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('freedash/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        let audioUnlocked = false;

        async function unlockAudio() {
            const up = document.getElementById("soundUp");
            const down = document.getElementById("soundDown");

            try {
                up.load();
                down.load();

                up.volume = 0.01;
                await up.play();
                up.pause();
                up.currentTime = 0;
                up.volume = 1.0;

                down.volume = 0.01;
                await down.play();
                down.pause();
                down.currentTime = 0;
                down.volume = 1.0;

                audioUnlocked = true;
                document.getElementById("audioModal").style.display = "none";
                console.log("🔊 Sons desbloqueados!");

            } catch (e) {
                console.warn("Primeira tentativa falhou:", e);
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    osc.connect(ctx.destination);
                    osc.start(0);
                    osc.stop(0);

                    audioUnlocked = true;
                    document.getElementById("audioModal").style.display = "none";
                    console.log("🔊 Sons desbloqueados via AudioContext!");
                } catch (err) {
                    console.error("Erro no fallback:", err);
                }
            }
        }

        function playUp() {
            if (!audioUnlocked) return;
            const a = document.getElementById("soundUp");
            a.currentTime = 0;
            a.volume = 1.0;
            a.play().catch(() => {});
        }

        function playDown() {
            if (!audioUnlocked) return;
            const a = document.getElementById("soundDown");
            a.currentTime = 0;
            a.volume = 1.0;
            a.play().catch(() => {});
        }

        function playTestSound() {
            // teste simples: toca som de aumento
            playUp();
        }

        function playTestSoundDown() {
            playDown();
        }

        function toggleFullscreen() {
            const doc = document.documentElement;
            if (!document.fullscreenElement) {
                doc.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        }

        // ====== Helpers de número/efeitos ======
        function parseBRLToNumber(str) {
            // "1.234,56" -> 1234.56
            if (typeof str !== 'string') return Number(str) || 0;
            return Number(str.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function formatBRL(num) {
            return num.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function animateValue(el, from, to, isMoney = false, duration = 800) {
            const start = performance.now();
            const diff = to - from;

            function step(now) {
                const p = Math.min((now - start) / duration, 1);
                const val = from + diff * p;
                el.textContent = isMoney ? formatBRL(val) : Math.round(val);
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        function pulseCard(cardId, direction) {
            const card = document.getElementById(cardId);
            if (!card) return;

            card.classList.add('pulse');
            if (direction === 'up') card.classList.add('flash-up');
            if (direction === 'down') card.classList.add('flash-down');

            setTimeout(() => {
                card.classList.remove('pulse', 'flash-up', 'flash-down');
            }, 900);
        }

        // ====== Estado anterior ======
        let lastDataNumeric = {
            clientes: 0,
            assinaturas: 0,
            faturamento: 0
        };

        function updateData() {
            fetch("{{ route('platform.kiosk.monitor.data') }}")
                .then(r => r.json())
                .then(data => {

                    // novos valores numéricos
                    const newClientes = Number(data.clientes) || 0;
                    const newAssinaturas = Number(data.assinaturas) || 0;
                    const newFaturamento = parseBRLToNumber(data.faturamento);

                    // ===== Clientes =====
                    if (newClientes !== lastDataNumeric.clientes) {
                        const dir = newClientes > lastDataNumeric.clientes ? 'up' : 'down';
                        animateValue(
                            document.getElementById("clientes"),
                            lastDataNumeric.clientes,
                            newClientes,
                            false
                        );
                        pulseCard('card-clientes', dir);
                        dir === 'up' ? playUp() : playDown();
                    }

                    // ===== Assinaturas =====
                    if (newAssinaturas !== lastDataNumeric.assinaturas) {
                        const dir = newAssinaturas > lastDataNumeric.assinaturas ? 'up' : 'down';
                        animateValue(
                            document.getElementById("assinaturas"),
                            lastDataNumeric.assinaturas,
                            newAssinaturas,
                            false
                        );
                        pulseCard('card-assinaturas', dir);
                        dir === 'up' ? playUp() : playDown();
                    }

                    // ===== Faturamento =====
                    if (newFaturamento !== lastDataNumeric.faturamento) {
                        const dir = newFaturamento > lastDataNumeric.faturamento ? 'up' : 'down';
                        animateValue(
                            document.getElementById("faturamento"),
                            lastDataNumeric.faturamento,
                            newFaturamento,
                            true
                        );
                        pulseCard('card-faturamento', dir);
                        dir === 'up' ? playUp() : playDown();
                    }

                    // atualiza estado
                    lastDataNumeric = {
                        clientes: newClientes,
                        assinaturas: newAssinaturas,
                        faturamento: newFaturamento
                    };
                })
                .catch(err => console.warn("Falha ao atualizar dados:", err));
        }

        updateData();
        setInterval(updateData, 3000);
    </script>

</body>

</html>
