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
class pjDispatcher extends pjObject
{
    public $ClassFile = __FILE__;
    private $controller;
    private $plugin = null;
    public $viewPath;
    public $templateName;
    public function __construct()
    {
    }
    
    public function dispatch(&$_request, $options)
    {
        
        $request = pjDispatcher::sanitizeRequest($_request);
        $controller = $this->createController($request);
        if ($controller !== false) {
            if (is_object($controller)) {
                $this->controller = &$controller;
                $tpl = &$controller->tpl;
                $controller->files = &$_FILES;
                if (isset($request["action"])) {
                    $action = $request["action"];
                    if (method_exists($controller, $action)) {
                        $bf = $controller->beforeFilter();
                        if (isset($request["params"])) {
                            $controller->params = $request["params"];
                        }
                        if ($bf) {
                            $result = $controller->$action();
                        }
                        $controller->afterFilter();
                        $controller->beforeRender();
                        $tpl["query"] = $controller->query;
                        $tpl["body"] = $controller->body;
                        $tpl["session"] = $controller->session;
                        $tpl["_get"] = $controller->_get;
                        $tpl["_post"] = $controller->_post;
                        $template = $action;
                        if (!is_null($controller->template)) {
                        }
                        $content_tpl = $this->getTemplate($request);
                    } else {
                        printf(
                            'Method <strong>%s::%s</strong> didn\'t exists',
                            $request["controller"],
                            $request["action"]
                        );
                        exit();
                    }
                } else {
                    $request["action"] = "pjActionIndex";
                    if ($controller->beforeFilter()) {
                        $controller->pjActionIndex();
                    }
                    $controller->afterFilter();
                    $controller->beforeRender();
                    $tpl["query"] = $controller->query;
                    $tpl["body"] = $controller->body;
                    $tpl["session"] = $controller->session;
                    $tpl["_get"] = $controller->_get;
                    $tpl["_post"] = $controller->_post;
                    $content_tpl = $this->getTemplate($request);
                }
                if (in_array("return", $options)) {
                    return $result;
                } elseif (in_array("output", $options)) {
                    return $tpl;
                } else {
                    if (!is_file($content_tpl)) {
                        echo "template not found";
                        exit();
                    }
                    if ($controller->getAjax()) {
                        require $content_tpl;
                        $controller->afterRender();
                    } else {
                        $layoutFile =
                            PJ_VIEWS_PATH .
                            "pjLayouts/" .
                            $controller->getLayout() .
                            ".php";
                        if (is_file($layoutFile)) {
                            require $layoutFile;
                        } else {
                            if ($this->plugin !== null) {
                                $layoutFile =
                                    pjObject::getConstant(
                                        $this->plugin,
                                        "PLUGIN_VIEWS_PATH"
                                    ) .
                                    "pjLayouts/" .
                                    $controller->getLayout() .
                                    ".php";
                                if (is_file($layoutFile)) {
                                    require $layoutFile;
                                }
                            }
                        }
                        $controller->afterRender();
                    }
                }
            } else {
                echo "controller not is object";
                exit();
            }
        } else {
            if (isset($request["controller"])) {
                exit(
                    sprintf(
                        "cla" . 'ss <strong>%s</strong> didn\'t exists',
                        $request["controller"]
                    )
                );
            } else {
                exit("cla" . 'ss didn\'t exists');
            }
        }
    }
    
    public function loadController($request)
    {
        
        $request = pjDispatcher::sanitizeRequest($request);
        $this->viewPath = PJ_VIEWS_PATH . $request["controller"] . "/";
        $plugin_folders = glob(PJ_PLUGINS_PATH . "*", GLOB_ONLYDIR);
        foreach ($plugin_folders as $folder) {
            $view_folders = glob($folder . "/views/" . "*", GLOB_ONLYDIR);
            if (!empty($view_folders)) {
                foreach ($view_folders as $vf) {
                    if ($vf == $folder . "/views/" . $request["controller"]) {
                        $this->plugin = str_replace(
                            PJ_PLUGINS_PATH,
                            "",
                            $folder
                        );
                        $this->viewPath = $vf . "/";
                        return $this;
                    }
                }
            }
        }
        return $this;
    }
    
    public function createController($request)
    {
        
        $request = pjDispatcher::sanitizeRequest($request);
        $this->loadController($request);
        if (class_exists($request["controller"])) {
            $pj_session = new pjSession();
            pjRegistry::getInstance()->set("reset_request", 0);
            $pj_get_input = new pjInput();
            pjRegistry::getInstance()->set("reset_request", 1);
            $pj_post_input = new pjInput();
            $pj_get_input = $pj_get_input->setMethod("get");
            $pj_post_input = $pj_post_input->setMethod("post");
            pjRegistry::getInstance()->set("_get", $pj_get_input);
            pjRegistry::getInstance()->set("_post", $pj_post_input);
            pjRegistry::getInstance()->set("session", $pj_session);
            $controller = new ($request["controller"])();
            $pj_post = $pj_post_input->raw();
            $pj_get = $pj_get_input->raw();
            $controller->session = $pj_session;
            $controller->_get = $pj_get_input;
            $controller->_post = $pj_post_input;
            $controller->body = &$pj_post;
            $controller->query = &$pj_get;
            return $controller;
        }
        return false;
    }
    
    public function getController()
    {
        
        return $this->controller;
    }
    
    public function getTemplate($request)
    {
        
        $request = pjDispatcher::sanitizeRequest($request);
        if (!is_null($this->controller->template)) {
            if (!strpos($this->controller->template["template"], ":")) {
                return PJ_VIEWS_PATH .
                    $this->controller->template["controller"] .
                    "/" .
                    $this->controller->template["template"] .
                    ".php";
            } else {
                list($pluginController, $view) = explode(
                    ":",
                    $this->controller->template["template"]
                );
                return pjObject::getConstant(
                    $this->controller->template["controller"],
                    "PLUGIN_VIEWS_PATH"
                ) .
                    "/" .
                    $pluginController .
                    "/" .
                    $view .
                    ".php";
            }
        } else {
            return $this->viewPath . $request["action"] . ".php";
        }
    }
    
    private static function sanitizeRequest($request)
    {
        
        $pattern = "/[^a-zA-Z0-9\_]/";
        if (isset($request["controller"])) {
            $request["controller"] = preg_replace(
                $pattern,
                "",
                basename($request["controller"])
            );
        }
        if (isset($request["action"])) {
            $request["action"] = preg_replace(
                $pattern,
                "",
                basename($request["action"])
            );
        }
        return $request;
    }
    
    private static function sanitizeOutput($buffer)
    {
       
        $search = ["/\>[^\S ]+/s", "/[^\S ]+\</s", "/(\s)+/s"];
        $replace = [">", "<", '\\1'];
        return preg_replace($search, $replace, $buffer);
    }
}
 ?>
