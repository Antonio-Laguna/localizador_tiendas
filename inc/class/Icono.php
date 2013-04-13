<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 15/12/12
 * Time: 13:24
 */

class Icono extends ORM
{
    public $id, $nombre, $icono;
    protected static $table = 'iconos';

    public function __construct($data){
        parent::__construct();
        if ($data && sizeof($data)) { $this->populateFromRow($data); }
    }

    public function populateFromRow($data){
        $this->id = isset($data['id']) ? intval($data['id']) : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
        $this->icono = isset($data['icono']) ? $data['icono'] : null;
    }
}
