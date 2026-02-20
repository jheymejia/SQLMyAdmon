<?php

declare(strict_types=1);

/**
 * SQLMyAdmin — Consola de Administración de Bases de Datos
 * Router y controlador principal.
 */

// ─── Autoload manual (sin Composer) ──────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\DatabaseService;
use Services\LoggerService;
use UI\ComponentRenderer;

// ─── Inicialización ──────────────────────────────────────────────────────────
session_start();

const LOG_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log';

$logger   = new LoggerService(LOG_FILE_PATH);
$renderer = new ComponentRenderer();

// ─── Router ──────────────────────────────────────────────────────────────────
$action = $_GET['action'] ?? '';

try {
    match ($action) {
        'connect'    => handleConnect($logger, $renderer),
        'dashboard'  => handleDashboard($logger, $renderer),
        'execute'    => handleExecute($logger, $renderer),
        'disconnect' => handleDisconnect($logger),
        default      => handleDefault($renderer),
    };
} catch (\Throwable $e) {
    $logger->error('Error no controlado', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    echo $renderer->renderLayout(
        'Error — SQLMyAdmin',
        <<<HTML
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="card p-8 max-w-lg w-full text-center">
                <div class="w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-2">Error Interno</h2>
                <p class="text-sm text-slate-400 font-mono break-all">{$e->getMessage()}</p>
                <a href="?" class="inline-block mt-6 btn-primary py-2 px-6 rounded-md text-white text-sm font-medium">Volver al inicio</a>
            </div>
        </div>
        HTML,
    );
}

// ─── Handlers ────────────────────────────────────────────────────────────────

/**
 * Muestra el formulario de conexión (vista por defecto).
 */
function handleDefault(ComponentRenderer $renderer): void
{
    // Si ya hay sesión activa, redirigir al dashboard
    if (hasActiveSession()) {
        header('Location: ?action=dashboard');
        exit;
    }

    echo $renderer->renderLayout(
        'Conexión — SQLMyAdmin',
        $renderer->renderLoginForm(),
    );
}

/**
 * Procesa el formulario de conexión.
 */
function handleConnect(LoggerService $logger, ComponentRenderer $renderer): void
{
    $host     = trim($_POST['host'] ?? '');
    $database = trim($_POST['database'] ?? '');
    $user     = trim($_POST['user'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación básica
    if ($host === '' || $database === '' || $user === '') {
        echo $renderer->renderLayout(
            'Conexión — SQLMyAdmin',
            $renderer->renderLoginForm('Todos los campos son obligatorios (excepto contraseña).'),
        );
        return;
    }

    // Intentar conexión
    $db = new DatabaseService($host, $database, $user, $password, $logger);

    try {
        $db->connect();
    } catch (\PDOException $e) {
        $logger->error('Fallo de conexión', [
            'host'     => $host,
            'database' => $database,
            'message'  => $e->getMessage(),
        ]);

        echo $renderer->renderLayout(
            'Conexión — SQLMyAdmin',
            $renderer->renderLoginForm('Error de conexión: ' . $e->getMessage()),
        );
        return;
    }

    // Guardar credenciales en sesión
    $_SESSION['db_credentials'] = [
        'host'     => $host,
        'database' => $database,
        'user'     => $user,
        'password' => $password,
    ];

    $db->disconnect();

    header('Location: ?action=dashboard');
    exit;
}

/**
 * Muestra el dashboard con la lista de tablas y el editor SQL.
 */
function handleDashboard(LoggerService $logger, ComponentRenderer $renderer): void
{
    if (!hasActiveSession()) {
        header('Location: ?');
        exit;
    }

    $db = createDatabaseService($logger);
    $db->connect();

    $tables = $db->getTableList();
    $db->disconnect();

    $database = $_SESSION['db_credentials']['database'];

    echo $renderer->renderLayout(
        "{$database} — SQLMyAdmin",
        $renderer->renderDashboard(
            tables: $tables,
            database: $database,
            queryArea: $renderer->renderQueryForm(),
            resultsArea: $renderer->renderEmptyState(),
        ),
    );
}

/**
 * Ejecuta una consulta SQL y muestra los resultados en el dashboard.
 */
function handleExecute(LoggerService $logger, ComponentRenderer $renderer): void
{
    if (!hasActiveSession()) {
        header('Location: ?');
        exit;
    }

    // La query puede venir por POST (formulario) o GET (click en tabla del sidebar)
    $sql = trim($_POST['sql'] ?? '');
    if ($sql === '' && isset($_GET['quick_query'])) {
        $sql = trim(urldecode($_GET['quick_query']));
    }

    $db = createDatabaseService($logger);
    $db->connect();

    $tables   = $db->getTableList();
    $database = $_SESSION['db_credentials']['database'];

    // Si no hay query, mostrar estado vacío
    if ($sql === '') {
        $db->disconnect();
        echo $renderer->renderLayout(
            "{$database} — SQLMyAdmin",
            $renderer->renderDashboard(
                tables: $tables,
                database: $database,
                queryArea: $renderer->renderQueryForm(),
                resultsArea: $renderer->renderEmptyState(),
            ),
        );
        return;
    }

    // Ejecutar
    $response = $db->executeQuery($sql);
    $db->disconnect();

    $resultsHtml = $response->isSuccess
        ? $renderer->renderResultsTable($response)
        : $renderer->renderErrorPanel($response);

    echo $renderer->renderLayout(
        "{$database} — SQLMyAdmin",
        $renderer->renderDashboard(
            tables: $tables,
            database: $database,
            queryArea: $renderer->renderQueryForm($sql),
            resultsArea: $resultsHtml,
        ),
    );
}

/**
 * Destruye la sesión y redirige al formulario de conexión.
 */
function handleDisconnect(LoggerService $logger): void
{
    $logger->info('Sesión cerrada por el usuario');

    $_SESSION = [];
    session_destroy();

    header('Location: ?');
    exit;
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Verifica si hay credenciales de conexión en sesión.
 */
function hasActiveSession(): bool
{
    return isset($_SESSION['db_credentials']) && is_array($_SESSION['db_credentials']);
}

/**
 * Instancia DatabaseService a partir de las credenciales en sesión.
 */
function createDatabaseService(LoggerService $logger): DatabaseService
{
    $creds = $_SESSION['db_credentials'];

    return new DatabaseService(
        host: $creds['host'],
        database: $creds['database'],
        user: $creds['user'],
        password: $creds['password'],
        logger: $logger,
    );
}
