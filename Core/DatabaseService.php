<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use PDOStatement;
use Services\LoggerService;

/**
 * Servicio principal de base de datos. Encapsula PDO con inyección de dependencias.
 */
class DatabaseService
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string        $host,
        private readonly string        $database,
        private readonly string        $user,
        private readonly string        $password,
        private readonly LoggerService $logger,
    ) {}

    /**
     * Establece la conexión PDO con configuración segura.
     *
     * @throws PDOException Si la conexión falla.
     */
    public function connect(): void
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $this->host, $this->database);

        $this->pdo = new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $this->logger->info('Conexión establecida', [
            'host'     => $this->host,
            'database' => $this->database,
            'user'     => $this->user,
        ]);
    }

    /**
     * Verifica si hay una conexión PDO activa.
     */
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }

    /**
     * Retorna el listado de tablas de la base de datos actual.
     *
     * @return string[]
     */
    public function getTableList(): array
    {
        $this->ensureConnection();

        /** @var PDOStatement $stmt */
        $stmt = $this->pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $this->logger->info('Listado de tablas obtenido', ['count' => count($tables)]);

        return $tables;
    }

    /**
     * Ejecuta una consulta SQL y retorna un QueryResponse con resultados o error.
     */
    public function executeQuery(string $sql): QueryResponse
    {
        $this->ensureConnection();

        $startTime = microtime(true);

        try {
            /** @var PDOStatement $stmt */
            $stmt = $this->pdo->query($sql);
            $executionTime = microtime(true) - $startTime;

            // Detectar si la consulta retorna resultados (SELECT, SHOW, DESCRIBE, EXPLAIN)
            $columnCount = $stmt->columnCount();

            if ($columnCount > 0) {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $columns = [];

                for ($i = 0; $i < $columnCount; $i++) {
                    $meta = $stmt->getColumnMeta($i);
                    $columns[] = $meta['name'] ?? "col_{$i}";
                }

                $response = QueryResponse::success(
                    data: $data,
                    columns: $columns,
                    rowCount: count($data),
                    executionTime: $executionTime,
                );
            } else {
                // INSERT, UPDATE, DELETE, etc.
                $response = QueryResponse::success(
                    data: [],
                    columns: [],
                    rowCount: $stmt->rowCount(),
                    executionTime: $executionTime,
                );
            }

            $this->logger->info('Consulta ejecutada', [
                'sql'           => mb_substr($sql, 0, 200),
                'rowCount'      => $response->rowCount,
                'executionTime' => round($executionTime, 4) . 's',
            ]);

            return $response;
        } catch (PDOException $e) {
            $executionTime = microtime(true) - $startTime;

            $this->logger->error('Error en consulta SQL', [
                'sql'       => mb_substr($sql, 0, 200),
                'errorCode' => $e->getCode(),
                'message'   => $e->getMessage(),
            ]);

            return QueryResponse::error(
                errorMessage: $e->getMessage(),
                errorCode: (string) $e->getCode(),
                executionTime: $executionTime,
            );
        }
    }

    /**
     * Cierra la conexión PDO.
     */
    public function disconnect(): void
    {
        $this->pdo = null;
        $this->logger->info('Conexión cerrada');
    }

    /**
     * Garantiza que la conexión esté activa antes de operar.
     *
     * @throws \RuntimeException Si no hay conexión activa.
     */
    private function ensureConnection(): void
    {
        if (!$this->isConnected()) {
            throw new \RuntimeException('No hay conexión activa a la base de datos.');
        }
    }
}
