<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 25/11/12
 * Time: 12:05
 */

class Tipo extends ORM
{
    public $id, $nombre;
    protected static $table = 'tipos';
 
    public function __construct($data){
        parent::__construct();
        if ($data && sizeof($data)) { $this->populateFromRow($data); }
    }
 
    public function populateFromRow($data){
        $this->id = isset($data['id']) ? intval($data['id']) : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
    }
}