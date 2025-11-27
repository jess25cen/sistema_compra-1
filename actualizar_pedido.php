<?php
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

echo "<h2>Actualizando estructura de tabla pedido_compra</h2>";

try {
    // Primero, obtener registros actuales para respaldarlos
    $sql_backup = "SELECT * FROM pedido_compra";
    $stmt = $conexion->prepare($sql_backup);
    $stmt->execute();
    $backup_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Registros encontrados: " . count($backup_data) . "</p>";
    
    // Eliminar la tabla antigua
    $sql_drop = "DROP TABLE IF EXISTS pedido_compra";
    $conexion->exec($sql_drop);
    echo "<p>✓ Tabla antigua eliminada</p>";
    
    // Crear la tabla nueva con PRIMARY KEY y AUTO_INCREMENT
    $sql_create = "CREATE TABLE `pedido_compra` (
      `pedido_compra` int(11) NOT NULL AUTO_INCREMENT,
      `fecha_compra` date NOT NULL,
      `estado` varchar(10) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      PRIMARY KEY (`pedido_compra`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conexion->exec($sql_create);
    echo "<p>✓ Tabla nueva creada con AUTO_INCREMENT</p>";
    
    // Re-insertar datos si los hay
    if (count($backup_data) > 0) {
        $sql_insert = "INSERT INTO pedido_compra (pedido_compra, fecha_compra, estado, id_usuario) 
                      VALUES (:pedido_compra, :fecha_compra, :estado, :id_usuario)";
        $stmt_insert = $conexion->prepare($sql_insert);
        
        foreach ($backup_data as $row) {
            $stmt_insert->execute([
                ':pedido_compra' => $row['pedido_compra'],
                ':fecha_compra' => $row['fecha_compra'],
                ':estado' => $row['estado'],
                ':id_usuario' => $row['id_usuario']
            ]);
        }
        echo "<p>✓ Datos restaurados: " . count($backup_data) . " registros</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>¡Actualización completada exitosamente!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
