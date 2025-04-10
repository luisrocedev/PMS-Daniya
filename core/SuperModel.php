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
     * Obtener todos los registros de una tabla
     */
    public function getAll($tabla)
    {
        $sql = "SELECT * FROM $tabla";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un registro por ID (asumiendo columna 'id_<tabla>' como PK)
     */
    public function getById($tabla, $id)
    {
        // Por convención, la PK se llama id_{nombre_tabla} (ej: id_empleado)
        $pk = 'id_' . rtrim($tabla, 's'); // Ejemplo: "empleados" => "id_empleado"
        $sql = "SELECT * FROM $tabla WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar un nuevo registro. $data es un array asociativo: ['columna' => 'valor', ...]
     */
    public function create($tabla, $data)
    {
        // Ej: $data = ['nombre' => 'Ana', 'apellidos' => 'García', ...]
        // Generamos dinámicamente la parte de "INSERT INTO $tabla (campo1, campo2, ...) VALUES (:campo1, :campo2, ...)"
        $columnas = array_keys($data);
        $columnasString = implode(', ', $columnas);
        $parametrosString = ':' . implode(', :', $columnas);

        $sql = "INSERT INTO $tabla ($columnasString) VALUES ($parametrosString)";
        $stmt = $this->pdo->prepare($sql);

        // Asignamos los valores
        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }

        return $stmt->execute();
    }

    /**
     * Actualizar un registro existente (basado en la PK)
     */
    public function update($tabla, $id, $data)
    {
        $pk = 'id_' . rtrim($tabla, 's');
        // Creamos el string de SET dinamicamente: "campo1 = :campo1, campo2 = :campo2, ..."
        $setPart = [];
        foreach ($data as $col => $val) {
            $setPart[] = "$col = :$col";
        }
        $setString = implode(', ', $setPart);

        $sql = "UPDATE $tabla SET $setString WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);

        // Bindeamos datos del array
        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }
        // Bindeamos la PK
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    /**
     * Eliminar un registro
     */
    public function delete($tabla, $id)
    {
        $pk = 'id_' . rtrim($tabla, 's');
        $sql = "DELETE FROM $tabla WHERE $pk = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
