<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 24/11/12
 * Time: 14:25
 */

abstract class DatabaseProvider
{
    protected $resource;
    public abstract function connect($host, $user, $pass, $dbname);
    public abstract function disconnect ();
    public abstract function getErrorNo();
    public abstract function getError();
    public abstract function query($q);
    public abstract function numRows($resource);
    public abstract function fetchArray($resource);
    public abstract function isConnected();
    public abstract function escape($var);
    public abstract function getInsertedID();
    public abstract function changeDB($database);
    public abstract function setCharset($charset);
}
class MySqlProvider extends DatabaseProvider
{
    public function connect($host, $user, $pass, $dbname){
        $this->resource = new mysqli($host, $user, $pass, $dbname);
        mysqli_set_charset($this->resource,'utf8');
        return  $this->resource;
    }
    public function disconnect(){
        return mysqli_close($this->resource);
    }
    public function getErrorNo(){
        return mysqli_errno($this->resource);
    }
    public function getError(){
        return mysqli_error($this->resource);
    }
    public function query($q){
        return mysqli_query($this->resource,$q);
    }
    public function numRows($resource){
        $num_rows = 0;

        if ($resource !== false && $resource !== true){
            $num_rows = mysqli_num_rows($resource);
        }

        return $num_rows;
    }
    public function fetchArray($result){
        return mysqli_fetch_assoc($result);
    }
    public function isConnected(){
        return !is_null($this->resource);
    }
    public function escape($var){
        return mysqli_real_escape_string($this->resource,$var);
    }
    public function getInsertedID(){
        return mysqli_insert_id($this->resource);
    }
    public function changeDB ($database){
        return mysqli_select_db($this->resource, $database);
    }
    public function setCharset($charset) {
        return mysqli_set_charset($this->resource,$charset);
    }
}

class Database
{
    public $provider;
   	private $params;
   	private static $_con;
   	public function __construct($provider,$host,$user,$pass,$table){
        if(!class_exists($provider)){
            throw new Exception("El proveedor especificado no ha sido implentado o añadido.");
        }

        $this->provider = new $provider;
        $this->provider->connect($host, $user, $pass, $table);

        if(!$this->provider->isConnected()){
            die ('¡No hemos podido conectar a la base de datos ¡'.$table.'!');
        }
        else {
            $this->provider->setCharset('utf-8');
        }
   	}
   	public static function getConnection($provider,$host,$user,$pass,$table){
   		if(self::$_con){
   			return self::$_con;
   		}
   		else{
   			$class = __CLASS__;
   			self::$_con = new $class($provider,$host,$user,$pass,$table);
   			return self::$_con;
   		}
   	}
   	private function replaceParams(){
   		$b=current($this->params);
   		next($this->params);
   		return $b;
   	}
   	private function prepare($sql, $params){
           $escaped = '';
           if ($params){
              foreach ($params as $key => $value){
                   if(is_bool($value)){
                       $value = $value ? 1:0;
                   }
                   elseif(is_double($value))
                       $value = str_replace(',', '.', $value);
                   elseif(is_numeric($value)){
                       if (is_string($value))
                           $value = "'".$this->provider->escape($value)."'";
                       else
                           $value = $this->provider->escape($value);
                   }
                   elseif(is_null($value))
                       $value = "NULL";
                   else
                       $value = "'".$this->provider->escape($value)."'";
                   $escaped[] = $value;
   		    }
           }
   		$this->params = $escaped;
   		$q = preg_replace_callback("/(\?)/i", array($this,"replaceParams"), $sql);

        error_log('About to execute this: '.$q);
   		return $q;
   	}
   	private function sendQuery($q, $params){
   		$query = $this->prepare($q, $params);
   		$result = $this->provider->query($query);
   		if($this->provider->getErrorNo()){
            error_log($this->provider->getError());
   		}
   		return $result;
   	}
   	public function executeScalar($q, $params=null){
   		$result = $this->sendQuery($q, $params);
   		if(!is_null($result)){
   			if(!is_object($result)){
   				return $result;
   			}
   			else{
   				$row = $this->provider->fetchArray($result);
   				return $row[0];
   			}
   		}
   		return null;
   	}
   	public function execute($q, $array_index = null, $params=null){
   		$result = $this->sendQuery($q, $params);
   		if((is_object($result) || $this->provider->numRows($result) || $result) && ($result !== true && $result !==
                   false)){
   			$arr = array();
   			while($row = $this->provider->fetchArray($result)){
                   if ($array_index)
                       $arr[$row[$array_index]] = $row;
                   else
   				    $arr[] = $row;
   			}
   			return $arr;
   		}

        if ($this->provider->getErrorNo())
            return false;
        else
            return true;
   	}
    public function changeDB ($database){
        $this->provider->changeDB($database);
    }
    public function getInsertedID(){
        return $this->provider->getInsertedID();
    }
    public function getError(){
        return $this->provider->getError();
    }
    public function __destruct(){
        $this->provider->disconnect();
    }
}
