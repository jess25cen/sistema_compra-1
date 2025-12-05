<?php
/**
 * Migración: añadir columna `id_condicion` en `factura_compra` justo
 * después de `fecha_vencimiento`, crear tabla `condicion_pago` y
 * asegurar las condiciones `Credito` y `Contado`.
 *
 * Ejecución: php migrate_add_condicion_factura.php
 */

require_once __DIR__ . '/conexion/db.php';

$db = new DB();
$c = $db->conectar();

try {
    $c->beginTransaction();

    // Crear tabla condicion_pago si no existe
    $sqlCreate = "CREATE TABLE IF NOT EXISTS condicion_pago (
        id_condicion INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(150) NOT NULL,
        descripcion VARCHAR(255) DEFAULT NULL,
        estado VARCHAR(20) DEFAULT 'ACTIVO',
        PRIMARY KEY (id_condicion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    $c->exec($sqlCreate);
    echo "OK: condicion_pago verificada/creada\n";

    // Insertar condiciones si no existen (Credito, Contado)
    $conds = ['Credito', 'Contado'];
    $stmtCheck = $c->prepare("SELECT id_condicion FROM condicion_pago WHERE nombre = :n LIMIT 1");
    $stmtIns = $c->prepare("INSERT INTO condicion_pago (nombre, descripcion, estado) VALUES (:n, :d, 'ACTIVO')");
    foreach ($conds as $cn) {
        $stmtCheck->execute([':n' => $cn]);
        if (!$stmtCheck->fetch()) {
            $stmtIns->execute([':n' => $cn, ':d' => null]);
            echo "Inserted condition: $cn\n";
        } else {
            echo "Condition exists: $cn\n";
        }
    }

    // Verificar columna en factura_compra
    $stmtCol = $c->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'factura_compra' AND COLUMN_NAME = 'id_condicion'");
    $stmtCol->execute();
    if (!$stmtCol->fetch()) {
        // Añadir columna justo después de fecha_vencimiento
        $c->exec("ALTER TABLE factura_compra ADD COLUMN id_condicion INT(11) NULL AFTER fecha_vencimiento;");
        echo "OK: columna id_condicion añadida AFTER fecha_vencimiento\n";

        // Añadir índice y FK (si no existe)
        $c->exec("ALTER TABLE factura_compra ADD INDEX idx_factura_condicion (id_condicion);");
        try {
            $c->exec("ALTER TABLE factura_compra ADD CONSTRAINT factura_compra_condicion_fk FOREIGN KEY (id_condicion) REFERENCES condicion_pago(id_condicion) ON DELETE SET NULL ON UPDATE CASCADE;");
            echo "OK: FK factura_compra -> condicion_pago creada\n";
        } catch (Exception $e) {
            echo "Aviso: no se pudo crear FK (posible duplicado): " . $e->getMessage() . "\n";
        }
    } else {
        echo "Info: columna id_condicion ya existe en factura_compra\n";
    }

    $c->commit();
    echo "Migración completada correctamente.\n";
} catch (Exception $e) {
    if ($c->inTransaction()) $c->rollBack();
    echo "ERROR en migración: " . $e->getMessage() . "\n";
    exit(1);
}

?>
