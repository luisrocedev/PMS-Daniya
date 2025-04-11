<?php
// core/SuperModel.php

require_once __DIR__ . '/Database.php';

class SuperModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Método auxiliar para obtener la clave primaria de una tabla.
     * Se incluye una condición específica para las tablas que no siguen
     * la convención "id_" + (nombre en singular), como es el caso de "habitaciones".
     */
    private function getPrimaryKey($tabla)
    {
        // Caso específico para la tabla "mantenimiento"
        if ($tabla === 'mantenimiento') {
            return 'id_incidencia';
        }
        // Caso específico para la tabla "habitaciones"
        if ($tabla === 'habitaciones') {
            return 'id_habitacion';
        }
        // Para el resto, se utiliza la convención básica: eliminar la "s" final
        return 'id_' . rtrim($tabla, 's');
    }

    /**
     * Obtener todos los registros de una tabla.
     */
    public function getAll($tabla)
    {
        $sql = "SELECT * FROM $tabla";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un registro por ID.
     */
    public function getById($tabla, $id)
    {
        $pk = $this->getPrimaryKey($tabla);
        $sql = "SELECT * FROM $tabla WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar un nuevo registro. 
     * $data es un array asociativo: ['columna' => 'valor', ...]
     */
    public function create($tabla, $data)
    {
        $columnas = array_keys($data);
        $columnasString = implode(', ', $columnas);
        $parametrosString = ':' . implode(', :', $columnas);

        $sql = "INSERT INTO $tabla ($columnasString) VALUES ($parametrosString)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }

        return $stmt->execute();
    }

    /**
     * Actualizar un registro existente (basado en la PK).
     */
    public function update($tabla, $id, $data)
    {
        $pk = $this->getPrimaryKey($tabla);
        $setPart = [];
        foreach ($data as $col => $val) {
            $setPart[] = "$col = :$col";
        }
        $setString = implode(', ', $setPart);

        $sql = "UPDATE $tabla SET $setString WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    /**
     * Eliminar un registro.
     */
    public function delete($tabla, $id)
    {
        $pk = $this->getPrimaryKey($tabla);
        $sql = "DELETE FROM $tabla WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
