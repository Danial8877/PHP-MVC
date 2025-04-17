<?php
class Core
{
    protected $controller = "HomeController";
    protected $method = "index";
    protected $params = [];

    public function __construct()
    {
        $url = $this->getUrl();

        if (!empty($url)) {
            $controllerName = ucwords($url[0]) . "Controller";
            if (file_exists("../app/controllers/" . $controllerName . ".php")) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                $this->errorUrl(404);
            }
        }

        require_once "../app/controllers/" . $this->controller . ".php";
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                $this->errorUrl(404);
            }
        }

        $this->params = $url ? array_values($url) : [];

        if (method_exists($this->controller, $this->method)) {
            $reflection = new ReflectionMethod($this->controller, $this->method);
            $requiredParams = $reflection->getNumberOfRequiredParameters();
            $totalParams = $reflection->getNumberOfParameters();

            if ($totalParams === 0 && !empty($this->params)) {
                $this->errorUrl(404);
            }

            if (count($this->params) < $requiredParams) {
                $this->errorUrl(404);
            }

            call_user_func_array([$this->controller, $this->method], $this->params);
        } else {
            $this->errorUrl(404);
        }
    }

    private function getUrl()
    {
        if (empty($_GET['url'])) {
            return [];
        }

        $url = trim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (preg_match('/\.\.|\\\\|%00|<|>|\$/i', $url)) {
            $this->errorUrl(403);
        }

        $url = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $url);
        $url = preg_replace('/\/+/', '/', $url);

        return array_slice(array_filter(explode('/', $url)), 0, 10);
    }

    private function errorUrl($code)
    {
        $error = new ErrorPage();
        $error->{"_{$code}_"}();
        exit;
    }
}
