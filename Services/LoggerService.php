<?php

declare(strict_types=1);

namespace Services;

/**
 * Niveles de log disponibles.
 */
enum LogLevel: string
{
    case INFO    = 'INFO';
    case WARNING = 'WARNING';
    case ERROR   = 'ERROR';
}

/**
 * Sistema de logs técnicos en archivo.
 * Registra conexiones, consultas, errores y tiempos de ejecución.
 */
class LoggerService
{
    public function __construct(
        private readonly string $logFilePath,
    ) {
        $directory = dirname($this->logFilePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Escribe una entrada de log con timestamp ISO-8601.
     *
     * @param LogLevel $level   Nivel de severidad.
     * @param string   $message Mensaje descriptivo.
     * @param array    $context Datos adicionales de contexto.
     */
    public function log(LogLevel $level, string $message, array $context = []): void
    {
        $timestamp = date('c'); // ISO-8601
        $contextString = $context !== [] ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';

        $entry = sprintf(
            "[%s] [%s] %s%s\n",
            $timestamp,
            $level->value,
            $message,
            $contextString,
        );

        file_put_contents($this->logFilePath, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Atajo para log de nivel INFO.
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Atajo para log de nivel WARNING.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Atajo para log de nivel ERROR.
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
}
