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
class pjController extends pjObject
{
    public $ClassFile = __FILE__;
    public $option_arr = [];
    public $tpl = [];
    private $js = [];
    private $css = [];
    public $defaultUser = "admin_user";
    public $layout = "pjActionDefault";
    public $template = null;
    public $ajax = false;
    public $params = [];
    public $body = [];
    public $query = [];
    public $session = [];
    public $files = [];
    public $_get = null;
    public $_post = null;
    public function __construct()
    {
    }
    
    public function beforeFilter()
    {
        
        return true;
    }
    
    public function beforeRender()
    {
    }
    
    public function afterFilter()
    {
        
        $this->_get->reassign_post_get();
        $this->_post->reassign_post_get();
    }
    
    public function afterRender()
    {
    }
    
    public function appendCss(
        $file,
        $path = PJ_CSS_PATH,
        $remote = false,
        $resolver = true
    ) {
       
        if ($resolver) {
            $resolve = self::dependencyResolver(
                debug_backtrace(),
                "appendCss",
                $path . $file
            );
            if ($resolve !== false) {
                extract($resolve);
            }
        }
        $this->css[] = compact("file", "path", "remote");
        return $this;
    }
    
    public function appendCssFromPlugin(
        $file,
        $library,
        $pluginName,
        $basePath = PJ_THIRD_PARTY_PATH,
        $baseDir = null
    ) {
        
        $dm = new pjDependencyManager($baseDir, $basePath);
        $dependencies =
            pjObject::getConstant($pluginName, "PLUGIN_DIR") .
            "config/dependencies.php";
        $dm->load($dependencies)->resolve();
        return $this->appendCss($file, $dm->getPath($library), false, false);
    }
    
    public function appendJs(
        $file,
        $path = PJ_JS_PATH,
        $remote = false,
        $resolver = true
    ) {
        
        if ($resolver) {
            $resolve = self::dependencyResolver(
                debug_backtrace(),
                "appendJs",
                $path . $file
            );
            if ($resolve !== false) {
                extract($resolve);
            }
        }
        $this->js[] = compact("file", "path", "remote");
        return $this;
    }
    
    public function appendJsFromPlugin(
        $file,
        $library,
        $pluginName,
        $basePath = PJ_THIRD_PARTY_PATH,
        $baseDir = null
    ) {
        
        $dm = new pjDependencyManager($baseDir, $basePath);
        $dependencies =
            pjObject::getConstant($pluginName, "PLUGIN_DIR") .
            "config/dependencies.php";
        $dm->load($dependencies)->resolve();
        return $this->appendJs($file, $dm->getPath($library), false, false);
    }
    
    private static function dependencyResolver($trace, $funcName, $filePath)
    {
        
        if (
            !(
                isset($trace[0], $trace[0]["function"]) &&
                $trace[0]["function"] == $funcName
            )
        ) {
            return false;
        }
        if (strpos($filePath, PJ_THIRD_PARTY_PATH) !== 0) {
            return false;
        }
        $className = get_class($trace[0]["object"]);
        $reflector = new ReflectionClass($className);
        $classFileName = $reflector->getFileName();
        $classFileName = str_replace("\\", "/", $classFileName);
        $pluginsPath = str_replace("\\", "/", PJ_PLUGINS_PATH);
        $isPlugin = false;
        if (strpos($classFileName, $pluginsPath) === 0) {
            $isPlugin = true;
        }
        $dependencies = [];
        if ($isPlugin) {
            $dependency_file =
                dirname(dirname($classFileName)) . "/config/dependencies.php";
            if (is_file($dependency_file)) {
                $dependencies = include $dependency_file;
            }
        } else {
            $dependency_file = PJ_CONFIG_PATH . "dependencies.php";
            if (is_file($dependency_file)) {
                $dependencies = include $dependency_file;
            }
        }
        if (!isset($dependencies) || empty($dependencies)) {
            return false;
        }
        $parts = explode("/", $filePath);
        $cnt = substr_count(PJ_THIRD_PARTY_PATH, "/");
        $library = $parts[$cnt];
        if (!isset($dependencies[$library])) {
            return false;
        }
        $file = join("/", array_slice($parts, $cnt + 1));
        $baseDir = defined("PJ_INSTALL_PATH") ? PJ_INSTALL_PATH : null;
        $dep = new pjDependencyManager($baseDir, PJ_THIRD_PARTY_PATH);
        $version = $dep
            ->load($dependencies)
            ->resolve()
            ->getVersion($library);
        $path = PJ_THIRD_PARTY_PATH . $library . "/" . $version . "/";
        return compact("file", "path");
    }
    
    public static function init($params = null)
    {
        
        $called_class = get_called_class();
        $instance = new $called_class();
        $instance->_get = pjRegistry::getInstance()->get("_get");
        $instance->_post = pjRegistry::getInstance()->get("_post");
        $instance->session = pjRegistry::getInstance()->get("session");
        $instance->params = $params != null ? $params : [];
        $instance->beforeFilter();
        $instance->afterFilter();
        $instance->beforeRender();
        $instance->afterRender();
        return $instance;
    }
    
    public function pjActionIndex()
    {
    }
    public function pjActionAfterInstall()
    {
    }
    public function pjActionBeforeInstall()
    {
    }
    
    public function pjActionCheckInstall()
    {
    }
    
    public function checkLogin()
    {
        
        if (!$this->isLoged()) {
            pjToolkit::redirect(
                $_SERVER["PHP_SELF"] .
                    "?controller=pjAdmin&action=pjActionLogin"
            );
        }
    }
    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->tpl;
        }
        if (array_key_exists($key, $this->tpl)) {
            return $this->tpl[$key];
        }
        return false;
    }
    
    public function getAjax()
    {
        
        return $this->ajax;
    }
    
    public function getCss()
    {
        
        return $this->css;
    }
    
    public function getJs()
    {
        
        return $this->js;
    }
    
    public function getLayout()
    {
        
        return $this->layout;
    }
    
    public function getParams()
    {
        
        return $this->params;
    }
    
    public function getUserId()
    {
        
        return isset($_SESSION[$this->defaultUser]) &&
            array_key_exists("id", $_SESSION[$this->defaultUser])
            ? $_SESSION[$this->defaultUser]["id"]
            : false;
    }
    
    public function getRoleId()
    {
        
        return isset($_SESSION[$this->defaultUser]) &&
            array_key_exists("role_id", $_SESSION[$this->defaultUser])
            ? $_SESSION[$this->defaultUser]["role_id"]
            : false;
    }
    
    public function isLoged()
    {
      
        if (
            isset($_SESSION[$this->defaultUser]) &&
            count($_SESSION[$this->defaultUser]) > 0
        ) {
            return true;
        }
        return false;
    }
    
    public function isAdmin()
    {
        return $this->getRoleId() == 1;
    }
    
    public function isXHR()
    {
        
        return @$_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest";
    }
    
    public function log($value)
    {
        
        $this->requestAction(
            [
                "controller" => "pjBaseLog",
                "action" => "pjActionLogger",
                "params" => [
                    "value" => $value,
                    "key" => md5($this->option_arr["private_key"] . PJ_SALT),
                ],
            ],
            ["return"]
        );
    }
    
    public function resetJs()
    {
        
        $this->js = [];
        return $this;
    }
    
    public function resetCss()
    {
        $this->css = [];
        return $this;
    }
    public function requestAction($request, $options = [])
    {
        $Dispatcher = new pjDispatcher();
        $dispatch = $Dispatcher->dispatch($request, $options);
        $v529 = in_array(version_compare(phpversion(), "5.2.9"), [0, 1]);
        $css = array_merge(
            $this->getCss(),
            $Dispatcher->getController()->getCss()
        );
        if ($v529) {
            $unique = array_unique($css, SORT_REGULAR);
        } else {
            $unique = array_unique($css);
        }
        $unique = array_map(
            "unserialize",
            array_unique(array_map("serialize", $css))
        );
        $this->setCss($unique);
        $js = array_merge(
            $this->getJs(),
            $Dispatcher->getController()->getJs()
        );
        if ($v529) {
            $unique = array_unique($js, SORT_REGULAR);
        } else {
            $unique = array_unique($js);
        }
        $unique = array_map(
            "unserialize",
            array_unique(array_map("serialize", $js))
        );
        $this->setJs($unique);
        return $dispatch;
    }
    public function set($key, $value)
    {
        $this->tpl[$key] = $value;
        return $this;
    }
    
    public function setAjax($value)
    {
        
        $this->ajax = (bool) $value;
        return $this;
    }
    
    public function setCss($value)
    {
        
        if (is_array($value)) {
            $this->css = $value;
        }
        return $this;
    }
    
    public function setJs($value)
    {
        
        if (is_array($value)) {
            $this->js = $value;
        }
        return $this;
    }
    
    public function setLayout($str)
    {
       
        $this->layout = $str;
        return $this;
    }
    
    public function setTemplate($controller, $template)
    {
        
        $this->template = compact("controller", "template");
        return $this;
    }
}
 ?>
