<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 15/12/12
 * Time: 12:27
 */
require_once ('Provincia.php');
 
class Poblacion extends ORM
{
    public $id, $nombre, $provincia, $obj_provincia;
    protected static $table = 'poblaciones';
 
    public function __construct($data){
        parent::__construct();
        if ($data && sizeof($data)) { $this->populateFromRow($data); }
    }
 
    public function populateFromRow($data){
        $this->id = isset($data['id']) ? intval($data['id']) : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $this->provincia = isset($data['provincia']) ? intval($data['provincia']) : null;
 
        if ($this->provincia){
            $this->obj_provincia = Provincia::find($this->provincia);
        }
    }
}