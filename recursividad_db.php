<?php 
declare(strict_types=1);
function conectar() : PDO 
{
    $dsn = 'mysql:host=127.0.0.1;port=3308;dbname=recursividad_db;charset=utf8mb4';
    $usuario = 'root';
    $contrasena = '';


    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO ($dsn, $usuario, $contrasena, $opciones);
}

function obtenerDescendientes(PDO $pdo, int $padreId):array
{
    $sql = 'SELECT id, nombre, padre_id
            FROM categorias
            WHERE padre_id = :padre_id
            ORDER BY nombre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':padre_id'=> $padreId]);
    $hijos = $stmt->fetchAll();
    
    
    if($hijos ===[]){
        return[];
    }


    $descendientes = [];

    foreach ($hijos as $hijo){
        //agregamos el hijo actual al resultado
        $descendientes[] = $hijo;

        //llamada recursiva: buscamos los descendientes de este hijo.
        $nietos = obtenerDescendientes($pdo, (int) $hijo['id']);

        //fusionamos los nietos con el resto de descendientes.
        $descendientes = array_merge($descendientes, $nietos);

    }
    return $descendientes;

}

 try {
    $pdo = conectar();

    $categoriaRaiz = 1; //"electronica
    $todos = obtenerDescendientes($pdo, $categoriaRaiz);

    if ($todos === []){
        echo "la categoria {$categoriaRaiz} no tiene descendientes.\n";
    } else {
        echo "Descendientes de la categoria {$categoriaRaiz} :\n";
        foreach ($todos as $cat){
            $sangria = str_repeat(' ',(int) $cat['padre_id'] === $categoriaRaiz ? 1 : 2);
            echo $sangria . "- {$cat['nombre']} (ID: {$cat['id']})\n";
        }
    }
   } catch (PDOException $e) {
        echo 'error de conexion o consulta; ' . $e->getMessage() . "\n";
    }
 
?>