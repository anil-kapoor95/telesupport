<?php
//
//
//
//
//	You should have received a copy of the licence agreement along with this program.
//
//	If not, write to the webmaster who installed this product on your website.
//
//	You MUST NOT modify this file. Doing so can lead to errors and crashes in the software.
//
//
//
//
?>
<?php
if (!defined("ROOT_PATH")) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
require_once dirname(__FILE__) . "/pjApps.class.php";
class pjDbDriver
{
    public $ClassFile = __FILE__;
    protected $charset = "utf8";
    protected $collation = "utf8_general_ci";
    protected $driver = "mysqli";
    protected $connectionId = false;
    protected $data = [];
    protected $database = null;
    protected $hostname = "localhost";
    protected $password = null;
    protected $persistent = false;
    protected $port = "3306";
    protected $result;
    protected $socket = null;
    protected $username = null;
    public function __construct($params = [])
    {
        if (is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }
    }
    
    public function getData($index = null)
    {
        
        return is_null($index) ? $this->data : $this->data[$index];
    }
    
    public function getResult()
    {
        
        return $this->result;
    }
    
    public function init()
    {
       
        if (
            is_resource($this->connectionId) ||
            $this->connectionId instanceof GdImage ||
            is_object($this->connectionId)
        ) {
            return true;
        }
        if (!$this->connect()) {
            return false;
        }
        if ($this->database != "" && $this->driver == "mysql") {
            if (!$this->selectDb()) {
                return false;
            }
        }
        if (!$this->setCharset($this->charset, $this->collation)) {
            return false;
        }
        return true;
    }
}
 ?>
