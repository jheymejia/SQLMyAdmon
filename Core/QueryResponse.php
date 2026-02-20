<?php

declare(strict_types=1);

namespace Core;

/**
 * DTO inmutable que estructura el resultado de una consulta SQL.
 */
class QueryResponse
{
    /**
     * @param bool    $isSuccess     Indica si la consulta se ejecutó sin errores.
     * @param array   $data          Filas resultantes (array de arrays asociativos).
     * @param array   $columns       Nombres de las columnas del resultado.
     * @param int     $rowCount      Cantidad de filas afectadas o retornadas.
     * @param float   $executionTime Tiempo de ejecución en segundos.
     * @param string  $errorMessage  Mensaje de error PDO (vacío si éxito).
     * @param string  $errorCode     Código SQLSTATE (vacío si éxito).
     */
    public function __construct(
        public bool   $isSuccess,
        public array  $data,
        public array  $columns,
        public int    $rowCount,
        public float  $executionTime,
        public string $errorMessage = '',
        public string $errorCode = '',
    ) {}

    /**
     * Crea una respuesta exitosa.
     */
    public static function success(array $data, array $columns, int $rowCount, float $executionTime): self
    {
        return new self(
            isSuccess: true,
            data: $data,
            columns: $columns,
            rowCount: $rowCount,
            executionTime: $executionTime,
        );
    }

    /**
     * Crea una respuesta de error.
     */
    public static function error(string $errorMessage, string $errorCode, float $executionTime): self
    {
        return new self(
            isSuccess: false,
            data: [],
            columns: [],
            rowCount: 0,
            executionTime: $executionTime,
            errorMessage: $errorMessage,
            errorCode: $errorCode,
        );
    }
}
