<?php

declare(strict_types=1);

namespace UI;

use Core\QueryResponse;

/**
 * Generador de HTML con diseño Shadcn UI (paleta Slate).
 * Toda la salida HTML del sistema se genera desde esta clase.
 */
class ComponentRenderer
{
    private const FONT_URL = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap';
    private const TAILWIND_URL = 'https://cdn.tailwindcss.com';

    /**
     * Shell HTML5 completo con Tailwind CDN y Google Fonts.
     */
    public function renderLayout(string $title, string $body): string
    {
        $fontUrl = self::FONT_URL;
        $tailwindUrl = self::TAILWIND_URL;

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es" class="dark">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="SQLMyAdmin — Consola de administración de bases de datos">
            <title>{$title}</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="{$fontUrl}" rel="stylesheet">
            <script src="{$tailwindUrl}"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class',
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Inter', 'system-ui', 'sans-serif'],
                                mono: ['JetBrains Mono', 'monospace'],
                            }
                        }
                    }
                }
            </script>
            <style>
                * { scrollbar-width: thin; scrollbar-color: #475569 #1e293b; }
                textarea:focus { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,0.5); }
                .table-container::-webkit-scrollbar { height: 8px; }
                .table-container::-webkit-scrollbar-track { background: #1e293b; }
                .table-container::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
                .fade-in { animation: fadeIn 0.3s ease-in-out; }
                @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
                .table-row-hover:hover { background-color: rgba(51, 65, 85, 0.5); }
                .btn-primary {
                    background: linear-gradient(135deg, #3b82f6, #2563eb);
                    transition: all 0.2s ease;
                }
                .btn-primary:hover {
                    background: linear-gradient(135deg, #60a5fa, #3b82f6);
                    box-shadow: 0 0 20px rgba(59,130,246,0.3);
                    transform: translateY(-1px);
                }
                .btn-danger {
                    background: linear-gradient(135deg, #ef4444, #dc2626);
                    transition: all 0.2s ease;
                }
                .btn-danger:hover {
                    background: linear-gradient(135deg, #f87171, #ef4444);
                    box-shadow: 0 0 20px rgba(239,68,68,0.3);
                    transform: translateY(-1px);
                }
                .card {
                    background: #0f172a;
                    border: 1px solid #1e293b;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -2px rgba(0,0,0,0.2);
                }
                .sidebar-item {
                    transition: all 0.15s ease;
                }
                .sidebar-item:hover {
                    background-color: #1e293b;
                    padding-left: 1rem;
                }
                .sidebar-item.active {
                    background-color: rgba(59,130,246,0.15);
                    border-left: 2px solid #3b82f6;
                    color: #60a5fa;
                }
            </style>
        </head>
        <body class="bg-slate-950 text-slate-200 font-sans antialiased min-h-screen">
            {$body}
        </body>
        </html>
        HTML;
    }

    /**
     * Formulario de conexión a base de datos.
     */
    public function renderLoginForm(?string $errorMessage = null): string
    {
        $errorHtml = '';
        if ($errorMessage !== null) {
            $safeError = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
            $errorHtml = <<<HTML
            <div class="fade-in bg-red-950/50 border border-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-red-300 text-sm font-medium">{$safeError}</p>
                </div>
            </div>
            HTML;
        }

        return <<<HTML
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center mb-8 fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 shadow-lg shadow-blue-500/20 mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">SQLMyAdmin</h1>
                    <p class="text-slate-400 text-sm mt-1">Consola de Administración de Bases de Datos</p>
                </div>

                <!-- Card -->
                <div class="card p-6 fade-in">
                    {$errorHtml}
                    <form method="POST" action="?action=connect" class="space-y-4" id="loginForm">
                        <div>
                            <label for="host" class="block text-sm font-medium text-slate-300 mb-1.5">Host</label>
                            <input type="text" id="host" name="host" value="localhost" required
                                   class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-md text-slate-200 text-sm font-mono placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                        </div>
                        <div>
                            <label for="database" class="block text-sm font-medium text-slate-300 mb-1.5">Base de Datos</label>
                            <input type="text" id="database" name="database" placeholder="nombre_db" required
                                   class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-md text-slate-200 text-sm font-mono placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                        </div>
                        <div>
                            <label for="user" class="block text-sm font-medium text-slate-300 mb-1.5">Usuario</label>
                            <input type="text" id="user" name="user" value="root" required
                                   class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-md text-slate-200 text-sm font-mono placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                   class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-md text-slate-200 text-sm font-mono placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                        </div>
                        <button type="submit" class="btn-primary w-full py-2.5 px-4 rounded-md text-white text-sm font-semibold mt-2">
                            Conectar
                        </button>
                    </form>
                </div>

                <p class="text-center text-slate-600 text-xs mt-6">PHP <?= PHP_VERSION ?> · PDO MySQL</p>
            </div>
        </div>
        HTML;
    }

    /**
     * Dashboard completo: sidebar + área de trabajo.
     */
    public function renderDashboard(
        array   $tables,
        string  $database,
        string  $queryArea,
        string  $resultsArea,
    ): string {
        $tableListHtml = $this->renderTableList($tables);

        $tableCount = count($tables);

        return <<<HTML
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-72 flex-shrink-0 bg-slate-900/50 border-r border-slate-800 flex flex-col">
                <!-- Header -->
                <div class="p-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-sm font-bold text-white truncate">SQLMyAdmin</h1>
                            <p class="text-xs text-slate-500 truncate font-mono">{$this->escape($database)}</p>
                        </div>
                    </div>
                </div>

                <!-- Tablas -->
                <div class="flex-1 overflow-y-auto">
                    <div class="p-3">
                        <div class="flex items-center justify-between mb-2 px-2">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tablas</span>
                            <span class="text-xs bg-slate-800 text-slate-400 px-2 py-0.5 rounded-full font-mono">{$tableCount}</span>
                        </div>
                        {$tableListHtml}
                    </div>
                </div>

                <!-- Desconectar -->
                <div class="p-3 border-t border-slate-800">
                    <a href="?action=disconnect"
                       class="btn-danger flex items-center justify-center gap-2 w-full py-2 px-4 rounded-md text-white text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Desconectar
                    </a>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-hidden">
                <!-- Query Area -->
                <div class="border-b border-slate-800 p-4">
                    {$queryArea}
                </div>

                <!-- Results Area -->
                <div class="flex-1 overflow-auto p-4">
                    {$resultsArea}
                </div>
            </main>
        </div>
        HTML;
    }

    /**
     * Lista de tablas clickeables para el sidebar.
     */
    public function renderTableList(array $tables): string
    {
        if ($tables === []) {
            return '<p class="text-sm text-slate-500 px-2 py-4 italic">No se encontraron tablas.</p>';
        }

        $items = '';
        foreach ($tables as $table) {
            $safeTable = $this->escape($table);
            $query = urlencode("SELECT * FROM `{$table}` LIMIT 50");
            $items .= <<<HTML
            <a href="?action=execute&quick_query={$query}"
               class="sidebar-item flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-slate-300 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span class="font-mono text-xs truncate">{$safeTable}</span>
            </a>
            HTML;
        }

        return '<nav class="space-y-0.5">' . $items . '</nav>';
    }

    /**
     * Editor SQL con textarea y botón de ejecución.
     */
    public function renderQueryForm(string $previousSql = ''): string
    {
        $safeSql = $this->escape($previousSql);

        return <<<HTML
        <form method="POST" action="?action=execute" id="queryForm">
            <div class="flex items-center gap-3 mb-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-200">Editor SQL</span>
                </div>
                <div class="flex-1"></div>
                <span class="text-xs text-slate-500">Ctrl + Enter para ejecutar</span>
                <button type="submit"
                        class="btn-primary flex items-center gap-2 py-1.5 px-4 rounded-md text-white text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ejecutar
                </button>
            </div>
            <textarea name="sql" id="sqlEditor" rows="5" placeholder="SELECT * FROM tabla LIMIT 10;"
                      class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-lg text-slate-200 text-sm font-mono placeholder-slate-600 resize-y focus:border-blue-500 transition-colors leading-relaxed"
            >{$safeSql}</textarea>
        </form>
        <script>
            document.getElementById('sqlEditor').addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('queryForm').submit();
                }
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = start + 4;
                }
            });
        </script>
        HTML;
    }

    /**
     * Tabla HTML dinámica con resultados de la consulta.
     */
    public function renderResultsTable(QueryResponse $response): string
    {
        $timeFormatted = number_format($response->executionTime * 1000, 2);

        // Para consultas sin resultado (INSERT, UPDATE, DELETE)
        if ($response->columns === []) {
            return <<<HTML
            <div class="fade-in card p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-green-300 font-medium text-sm">Consulta ejecutada correctamente</p>
                        <p class="text-slate-400 text-xs mt-0.5">
                            {$response->rowCount} fila(s) afectada(s) · {$timeFormatted}ms
                        </p>
                    </div>
                </div>
            </div>
            HTML;
        }

        // Encabezados
        $headerCells = '';
        foreach ($response->columns as $col) {
            $safeCol = $this->escape($col);
            $headerCells .= "<th class=\"px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider whitespace-nowrap bg-slate-900/80\">{$safeCol}</th>";
        }

        // Filas
        $rows = '';
        foreach ($response->data as $row) {
            $cells = '';
            foreach ($response->columns as $col) {
                $value = $row[$col] ?? null;
                if ($value === null) {
                    $cells .= '<td class="px-4 py-2.5 text-xs whitespace-nowrap"><span class="text-slate-600 italic">NULL</span></td>';
                } else {
                    $safeValue = $this->escape((string) $value);
                    $displayValue = mb_strlen((string) $value) > 100
                        ? mb_substr($safeValue, 0, 100) . '…'
                        : $safeValue;
                    $cells .= "<td class=\"px-4 py-2.5 text-xs font-mono text-slate-300 whitespace-nowrap\">{$displayValue}</td>";
                }
            }
            $rows .= "<tr class=\"table-row-hover border-b border-slate-800/50 transition-colors\">{$cells}</tr>";
        }

        return <<<HTML
        <div class="fade-in space-y-3">
            <!-- Status bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-400 bg-green-500/10 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                        Éxito
                    </span>
                    <span class="text-xs text-slate-400">{$response->rowCount} fila(s) · {$timeFormatted}ms</span>
                </div>
                <span class="text-xs text-slate-500">{$this->escape(count($response->columns) . ' columna(s)')}</span>
            </div>

            <!-- Table -->
            <div class="card overflow-hidden">
                <div class="table-container overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-700">{$headerCells}</tr>
                        </thead>
                        <tbody>{$rows}</tbody>
                    </table>
                </div>
            </div>
        </div>
        HTML;
    }

    /**
     * Panel de error técnico con código y mensaje PDO.
     */
    public function renderErrorPanel(QueryResponse $response): string
    {
        $timeFormatted = number_format($response->executionTime * 1000, 2);
        $safeMessage = $this->escape($response->errorMessage);
        $safeCode = $this->escape($response->errorCode);

        return <<<HTML
        <div class="fade-in space-y-3">
            <!-- Status bar -->
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                    Error
                </span>
                <span class="text-xs text-slate-400">{$timeFormatted}ms</span>
            </div>

            <!-- Error card -->
            <div class="bg-red-950/30 border border-red-900/50 rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-red-900/30 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-red-300">Error de Ejecución SQL</span>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SQLSTATE</span>
                        <p class="text-sm font-mono text-red-300 mt-1 bg-red-950/50 px-3 py-2 rounded-md">{$safeCode}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mensaje</span>
                        <p class="text-sm font-mono text-red-300 mt-1 bg-red-950/50 px-3 py-2 rounded-md leading-relaxed break-all">{$safeMessage}</p>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }

    /**
     * Placeholder para cuando no hay resultados que mostrar.
     */
    public function renderEmptyState(): string
    {
        return <<<HTML
        <div class="flex flex-col items-center justify-center h-full text-center py-16 fade-in">
            <div class="w-16 h-16 rounded-full bg-slate-800/50 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-slate-500 text-sm font-medium">Escribe una consulta SQL para comenzar</p>
            <p class="text-slate-600 text-xs mt-1">O haz clic en una tabla del sidebar para explorar</p>
        </div>
        HTML;
    }

    /**
     * Escapa string para prevenir XSS.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
