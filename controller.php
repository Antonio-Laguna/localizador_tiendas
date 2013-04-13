<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 24/11/12
 * Time: 14:52
 */
if (isset($_POST['accion']) || isset($_GET['accion'])){
    require_once('config.php');
    require_once('inc/class/Utils.php');
    require_once('inc/class/ORM.php');
    require_once('inc/class/DAO.php');
    require_once('inc/class/Tienda.php');
 
    DAO::$database = Database::getConnection(DB_PROVIDER, DB_HOST, DB_USER, DB_PASSWORD, DB_DB);
 
    $action = isset($_POST['accion']) ? $_POST['accion'] :$_GET['accion'];
    $data = isset($_POST['accion']) ? $_POST :$_GET;
 
    date_default_timezone_set('Europe/Madrid');
    header('Content-Type: application/json');
 
    switch ($action){
        case 'obtenerProvincias' :
            echo json_encode(DAO::obtenerProvincias());
            break;
        case 'obtenerPoblaciones' :
            echo json_encode(DAO::obtenerPoblacionesPorProvincia($data['provincia']));
            break;        
        case 'obtenerTiendas' :
            echo json_encode(DAO::obtenerTiendasPorPoblacion($data['poblacion']));
            break;        
        case 'obtenerTiendasCercanas':
            echo json_encode(
                DAO::obtenerTiendasCercanas($data['provincia'],$data['poblacion'],$data['lat'], $data['lng'])
            );
            break;
        default :
            echo json_encode(array('error' => true, 'mensaje' => 'Acción no implementada'));
            break;
    }
}
else {
    header($_SERVER['SERVER_PROTOCOL'] ." 400 Bad Request",true,400);
}