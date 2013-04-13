<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 24/11/12
 * Time: 14:22
 */

// Including Database Layer
require_once(dirname(__FILE__) . '/inc/class/Database.php');

//URL GENERICA 
define ( 'RUTA_ABSOLUTA', 'http://marbleinteractive.com/webapps/nov/');

// Database
define ( 'DB_HOST', 'localhost' );
define ( 'DB_USER', 'root' );
define ( 'DB_PASSWORD', '' );
define ( 'DB_DB', 'tiendas' );
define ( 'DB_PROVIDER', 'MySqlProvider');

// Configuration

define ( 'DISTANCE_THRESHOLD', '5'); //Km