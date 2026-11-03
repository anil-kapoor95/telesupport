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
class pjObject
{
    const PJ_FRAMEWORK_VERSION = "2.0";
    const PJ_FRAMEWORK_BUILD = "2.0.4";
    public $ClassFile = __FILE__;
    
    public static function escapeString($value)
    {
       
        $registry = pjRegistry::getInstance();
        if ($registry->is("dbo")) {
            $dbo = $registry->get("dbo");
            if (is_object($dbo) && method_exists($dbo, "escapeString")) {
                return $dbo->escapeString($value);
            }
        }
        if (
            !defined("PJ_HOST") ||
            !defined("PJ_USER") ||
            !defined("PJ_PASS") ||
            !defined("PJ_DB")
        ) {
            $search = ["\\", "\x00", "\n", "\r", "'", '"', "\x1a"];
            $replace = ["\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z"];
            return str_replace($search, $replace, $value);
        }
        $driver = function_exists("mysqli_connect")
            ? "pjMysqliDriver"
            : "pjMysqlDriver";
        $params = [
            "hostname" => PJ_HOST,
            "username" => PJ_USER,
            "password" => PJ_PASS,
            "database" => PJ_DB,
        ];
        if (strpos($params["hostname"], ":") !== false) {
            list($hostname, $value) = explode(":", $params["hostname"]);
            if (preg_match("/\D/", $value)) {
                $params["socket"] = $value;
            } else {
                $params["port"] = $value;
            }
            $params["hostname"] = $hostname;
        }
        $dbo = pjSingleton::getInstance($driver, $params);
        if (!$dbo->init()) {
            return $value;
        }
        return $dbo->escapeString($value);
    }
    
    public static function import($type, $name)
    {
        
        $type = strtolower($type);
        if (!in_array($type, ["model", "component", "plugin"])) {
            return false;
        }
        switch ($type) {
            case "model":
            case "component":
                break;
            case "plugin":
                if (is_array($name)) {
                    foreach ($name as $n) {
                        $configFile =
                            PJ_PLUGINS_PATH . $n . "/config/config.inc.php";
                        if (is_file($configFile)) {
                            require_once $configFile;
                        }
                    }
                } else {
                    $configFile =
                        PJ_PLUGINS_PATH . $name . "/config/config.inc.php";
                    if (is_file($configFile)) {
                        require_once $configFile;
                    }
                }
                break;
        }
        return;
    }
    
    public static function getPlugin($name)
    {
        
        $registry = pjRegistry::getInstance();
        if (null !== $registry->get($name)) {
            return $name;
        }
        $plugins = $registry->get("plugins");
        if (is_array($plugins)) {
            foreach ($plugins as $plugin => $controllers) {
                if (in_array($name, $controllers)) {
                    return $plugin;
                }
            }
        }
        return null;
    }
    
    public static function getConstant($plugin, $const)
    {
        
        $registry = pjRegistry::getInstance();
        $config = $registry->get($plugin);
        return isset($config[$const]) ? $config[$const] : null;
    }
    
    public static function getFrameworkVersion()
    {
        
        return self::PJ_FRAMEWORK_VERSION;
    }
    
    public static function getFrameworkBuild()
    {
        
        return self::PJ_FRAMEWORK_BUILD;
    }
}
 ?>
