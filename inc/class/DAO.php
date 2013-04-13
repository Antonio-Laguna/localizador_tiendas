<?php
class DAO
{
    public static $database;
 
    public static function obtenerProvincias() {
        return Provincia::all(' ORDER BY nombre');
    }
 
    public static function obtenerPoblacionesPorProvincia($provincia) {
        $results = Poblacion::where('provincia',$provincia);
         
        if ($results) {
            $poblaciones = null;
            foreach ($results as $index => $poblacion) {
                $poblaciones[] = array(
                    'id' => $poblacion->id,
                    'nombre' => $poblacion->nombre
                );
            }
            $result = $poblaciones;
        }
        else {
            $result = array('error' => true, 'mensaje' => 'No hay poblaciones para esa provincia');
        }
 
        return $result;
    }    
 
    public static function obtenerTiendasPorPoblacion($poblacion) {
        $query = 'SELECT tiendas.id, nombre_comercial, tipos.nombre AS tipo, direccion, cp,
                        poblaciones.nombre AS poblacion, lat, lng
                        FROM tiendas
                    INNER JOIN tipos ON tiendas.tipo = tipos.id
                    INNER JOIN poblaciones ON tiendas.poblacion = poblaciones.id
                    WHERE poblacion = ?';
        $results = self::$database->execute($query,null,array($poblacion));
 
        if ($results) {
            $result = $results;
        }
        else {
            $result = array('error' => true, 'mensaje' => 'No hay tiendas para esa poblacion');
        }
 
        return $result;
    }       
 
    public static function obtenerTiendasCercanas($provincia, $poblacion, $lat, $lng){
        $query = 'SELECT tiendas.id, nombre_comercial, tipos.nombre AS tipo, direccion, cp,
                    poblaciones.nombre AS poblacion, lat, lng,
                    provincias.nombre AS provincia
                    FROM tiendas
                INNER JOIN tipos ON tiendas.tipo = tipos.id
                INNER JOIN poblaciones ON tiendas.poblacion = poblaciones.id
                INNER JOIN provincias ON poblaciones.provincia = provincias.id
                WHERE poblaciones.nombre = ?';
 
        $results = self::$database->execute($query,null,array($poblacion));
 
        if (!$results) {
            //Lo intentamos con la provincia
            $query = 'SELECT tiendas.id, nombre_comercial, tipos.nombre AS tipo, direccion, cp,
                        poblaciones.nombre AS poblacion,
                        provincias.nombre AS provincia,
                        lat, lng
                        FROM tiendas
                    INNER JOIN tipos ON tiendas.tipo = tipos.id
                    INNER JOIN poblaciones ON tiendas.poblacion = poblaciones.id
                    INNER JOIN provincias ON poblaciones.provincia = provincias.id
                    WHERE provincias.nombre = ?';
 
            $results = self::$database->execute($query,null,array($provincia));
        }
 
        if ($results){
            $result = null;
 
            foreach ($results as $index => $store) {
                $distance = Utils::getDistance($lat,$lng,$store['lat'],$store['lng']);
                if ($distance <= DISTANCE_THRESHOLD){
                    $store['distancia'] = round($distance,2);
                    $result[] = $store;
                }
            }
 
            $distances = array();
            foreach ($result as $key => $store){
                $distances[$key] = $store['distancia'];
            }
            array_multisort($distances, SORT_ASC, $result);
 
            if (!$result) {
                $result = array('error' => true, 'mensaje' => 'No hay tiendas cercanas en la ubicación actual');
            }
        }
        else {
            $result = array('error' => true, 'mensaje' => 'No hay tiendas cercanas en la ubicación actual');
        }
 
        return $result;
    }
}