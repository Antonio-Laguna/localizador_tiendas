<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 15/12/12
 * Time: 12:27
 */
class Provincia extends ORM
{
    public $id, $nombre;
    protected static $table = 'provincias';
 
    public function __construct($data){
        parent::__construct();
        if ($data && sizeof($data)) { $this->populateFromRow($data); }
    }
 
    public function populateFromRow($data){
        $this->id = isset($data['id']) ? intval($data['id']) : null;
        $this->nombre = isset($data['nombre']) ? $data['nombre'] : null;
    }
}