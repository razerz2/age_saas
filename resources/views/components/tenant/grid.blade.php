@props([
    'id',
    'columns' => [], // pode ser array PHP ou string JS
    'ajaxUrl' => null,
    'data' => [], // pode ser array PHP ou string JS
    'pagination' => true,
    'search' => true,
    'sort' => true,
])

@php
    $tableId = $id;
    $paginationEnabled = $pagination ? 'true' : 'false';
    $searchEnabled = $search ? 'true' : 'false';
    $sortEnabled = $sort ? 'true' : 'false';
    $hasAjax = !empty($ajaxUrl);

    // Normalizar colunas para o formato Grid.js { id, name, formatter? }
    $normalizedColumns = [];
    foreach ($columns as $col) {
        if (is_array($col)) {
            $colId = $col['name'] ?? ($col['id'] ?? null);
            $colLabel = $col['label'] ?? ($col['name'] ?? $colId);

            if (!$colId) {
                continue;
            }

            $entry = [
                'id' => $colId,
                'name' => $colLabel,
            ];

            if ($colId === 'actions') {
                // Formatter para renderizar HTML das ações
                $entry['formatter'] = 'html';
            }

            $normalizedColumns[] = $entry;
        }
    }
@endphp

<div id="{{ $tableId }}" class="w-full"></div>

@pushOnce('styles')
    <style>
        /* Grid.js + TailAdmin basic skin */
        /* ===== GRID WRAPPER ===== */
        .gridjs-wrapper {
            border-radius: 0.75rem;
            border: 1px solid rgba(209, 213, 219, 1);
            overflow: hidden;
            margin-bottom: 1rem;
            background-color: #ffffff;
        }

        .dark .gridjs-wrapper {
            border-color: rgba(55, 65, 81, 1);
            background-color: #0f172a;
        }
        .gridjs-table {
            width: 100%;
            font-size: 0.875rem;
        }
        .gridjs-thead {
            background-color: #f9fafb;
        }
        .dark .gridjs-thead {
            background-color: #111827;
        }
        .gridjs-th {
            padding: 0.75rem 1rem;
            font-weight: 600;
            color: #4b5563;
        }
        .dark .gridjs-th {
            color: #d1d5db;
        }
        .gridjs-td {
            padding: 0.75rem 1rem;
            color: #111827;
        }
        .dark .gridjs-td {
            color: #e5e7eb;
        }
        .gridjs-tr:nth-child(even) .gridjs-td {
            background-color: #f9fafb;
        }
        .dark .gridjs-tr:nth-child(even) .gridjs-td {
            background-color: #030712;
        }
        /* ===== FOOTER ===== */
        .gridjs-footer {
            border-top: 1px solid rgba(209, 213, 219, 1);
            padding: 0.75rem 1rem !important;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark .gridjs-footer {
            border-top-color: rgba(55, 65, 81, 1);
        }

        /* ===== PAGINATION LAYOUT ===== */
        .gridjs-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        /* summary à esquerda */
        .gridjs-summary {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .gridjs-summary {
            color: #9ca3af;
        }

        /* botões totalmente à direita */
        .gridjs-pages {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
        }

        /* ===== BOTÕES ===== */
        .gridjs-pagination .gridjs-pages button {
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            background-color: #f9fafb;
            color: #111827;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .gridjs-pagination .gridjs-pages button:hover {
            background-color: #e5e7eb;
        }

        .dark .gridjs-pagination .gridjs-pages button {
            background-color: #1f2937;
            border-color: #374151;
            color: #d1d5db;
        }

        .dark .gridjs-pagination .gridjs-pages button:hover {
            background-color: #374151;
        }

        .gridjs-pagination .gridjs-pages button.current {
            background-color: #2563eb;
            color: #ffffff;
            border-color: transparent;
        }
    </style>
@endPushOnce

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.gridjs) {
                console.error('Grid.js CDN not loaded');
                return;
            }

            const target = document.getElementById(@json($tableId));
            if (!target) return;

            const rawColumns = @json($normalizedColumns);
            const columns = rawColumns.map(col => {
                const isStatus  = col.id === 'status_badge';
                const isActions = col.id === 'actions';

                return {
                    id: col.id,
                    name: col.name,
                    formatter: (cell) => {
                        if (isStatus || isActions) {
                            return gridjs.html(cell);
                        }
                        return cell;
                    }
                };
            });

            const baseConfig = {
                columns: columns,
                sort: {{ $sortEnabled }},
                search: {{ $searchEnabled }},
                language: {
                    search: {
                        placeholder: 'Pesquisar...'
                    },
                    pagination: {
                        previous: 'Anterior',
                        next: 'Próxima',
                    },
                    loading: 'Carregando...',
                    noRecordsFound: 'Nenhum registro encontrado',
                    error: 'Erro ao carregar dados',
                },
                className: {
                    table: 'w-full text-left',
                },
                style: {
                    th: {
                        'background-color': 'transparent',
                    }
                },
            };

            @if($hasAjax)
                const serverConfig = {
                    columns: columns,
                    sort: {{ $sortEnabled }},
                    search: {{ $searchEnabled }},
                    pagination: {
                        enabled: true,
                        limit: 10
                    },
                    server: {
                        url: @json($ajaxUrl),
                        method: 'GET',
                        then: data => data.data,
                        total: data => data.meta.total
                    },
                    language: {
                        search: { placeholder: 'Pesquisar...' },
                        pagination: { previous: 'Anterior', next: 'Próxima' },
                        loading: 'Carregando...',
                        noRecordsFound: 'Nenhum registro encontrado',
                        error: 'Erro ao carregar dados',
                    },
                    className: { table: 'w-full text-left' }
                };

                new gridjs.Grid(serverConfig).render(target);
            @else
                const staticData = @json($data);
                const clientConfig = Object.assign({}, baseConfig, {
                    pagination: {
                        enabled: true,
                        limit: 10,
                    },
                    data: staticData,
                });

                new gridjs.Grid(clientConfig).render(target);
            @endif
        });
    </script>
@endpush
