<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 15/12/12
 * Time: 14:03
 */
require_once('Tipo.php');
require_once('Poblacion.php');
 
class Tienda extends ORM
{
    public $id, $sfid, $nombre_comercial, $tipo, $obj_tipo, $direccion, 
           $cp, $poblacion, $obj_poblacion, $lat, $lng, $texto_promocion, 
           $texto_animacion, $distancia;
            
    protected static $table = 'tiendas';
 
    public function __construct($data){
        parent::__construct();
        if ($data && sizeof($data)) { $this->populateFromRow($data); }
    }
 
    public function populateFromRow($data){
        $this->id = isset($data['id']) ? intval($data['id']) : null;
        $this->sfid = isset($data['nombre']) ? $data['nombre'] : null;
        $this->nombre_comercial = isset($data['nombre_comercial']) ? $data['nombre_comercial'] : null;
 
        $this->tipo = isset($data['tipo']) ? intval($data['tipo']) : null;
 
        if ($this->tipo && !isset($data['no_deep'])){
            $this->obj_tipo = Tipo::find($this->tipo);
        }
 
        $this->direccion = isset($data['direccion']) ? $data['direccion'] : null;
        $this->cp = isset($data['cp']) ? $data['cp'] : null;
 
        $this->poblacion = isset($data['poblacion']) ? intval($data['poblacion']) : null;
        if ($this->poblacion && !isset($data['no_deep'])) {
            $this->obj_poblacion = Poblacion::find($this->poblacion);
        }
 
        $this->lat = isset($data['lat']) ? $data['lat'] : null;
        $this->lng = isset($data['lng']) ? $data['lng'] : null;
 
        $this->texto_promocion = isset($data['texto_promocion']) ? $data['texto_promocion'] : null;
        $this->texto_animacion = isset($data['texto_animacion']) ? $data['texto_animacion'] : null;
    }
}