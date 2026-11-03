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
class pjMysqliDriver extends pjDbDriver
{
    public $ClassFile = __FILE__;
    protected $driver = "mysqli";
    private $useSetNames;
    
    public function affectedRows()
    {
       
        return mysqli_affected_rows(
            $this->connectionId !== false ? $this->connectionId : null
        );
    }
    
    public function connect()
    {
       
        $this->connectionId = @mysqli_connect(
            $this->hostname,
            $this->username,
            $this->password,
            $this->database,
            !is_null($this->port) ? $this->port : null,
            !is_null($this->socket) ? $this->socket : null
        );
        return $this->connectionId;
    }
    
    public function connectError()
    {
        
        return mysqli_connect_error();
    }
    
    public function error()
    {
        
        return mysqli_error($this->connectionId);
    }
    
    public function escapeString($value)
    {
       
        if (defined("PHP_MAJOR_VERSION") && PHP_MAJOR_VERSION < 7) {
            // if (get_magic_quotes_gpc()) {
            //     $value = stripslashes($value);
            // }
            $value = stripslashes($value);
        }
        if (
            function_exists("mysqli_real_escape_string") &&
            is_object($this->connectionId)
        ) {
            return mysqli_real_escape_string($this->connectionId, $value);
        } elseif (function_exists("mysql_real_escape_string")) {
            return mysql_real_escape_string($value);
        } else {
            trigger_error(
                "Function mysqli_real_escape_string not found.",
                E_USER_ERROR
            );
        }
    }
    
    public function fetchArray()
    {
        
        if (is_object($this->result)) {
            $this->data = [];
            while ($row = mysqli_fetch_array($this->result)) {
                $this->data[] = $row;
            }
            $this->freeResult();
        }
        return $this;
    }
    
    public function fetchAssoc()
    {
        
        if (is_object($this->result)) {
            $this->data = [];
            while ($row = mysqli_fetch_assoc($this->result)) {
                $this->data[] = $row;
            }
            $this->freeResult();
        }
        return $this;
    }
    
    public function fetchRow()
    {
        
        if (is_object($this->result)) {
            $this->data = [];
            $this->data = mysqli_fetch_row($this->result);
        }
        return $this;
    }
    
    public function freeResult()
    {
        
        if (is_object($this->result)) {
            mysqli_free_result($this->result);
            return true;
        }
        return false;
    }
    
    public function insertId()
    {
        
        return mysqli_insert_id($this->connectionId);
    }
    
    public function numRows()
    {
        
        if (is_object($this->result)) {
            return @mysqli_num_rows($this->result);
        }
        return false;
    }
    
    public function query($query)
    {
        
        $this->result = mysqli_query($this->connectionId, $query);
        return $this->result;
    }
    
    public function selectDb()
    {
        
        return mysqli_select_db($this->connectionId, $this->database);
    }
    
    protected function setCharset($charset, $collation)
    {
       
        if (!isset($this->useSetNames)) {
            $this->useSetNames = version_compare(
                mysqli_get_server_info($this->connectionId),
                "5.0.7",
                ">="
            )
                ? false
                : true;
        }
        if ($this->useSetNames === true) {
            return $this->query(
                "SET NAMES '" .
                    $this->escapeString($charset) .
                    "' COLLATE '" .
                    $this->escapeString($collation) .
                    "'"
            );
        } else {
            return @mysqli_set_charset($this->connectionId, $charset);
        }
    }
}
 ?>
