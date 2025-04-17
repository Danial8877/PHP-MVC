<?php

class Controller
{
    public function model($model)
    {
        require_once "../app/models/" . ucwords($model) . ".php";
        return new $model;
    }
    public function view($view, $data = [])
    {
        extract($data, EXTR_SKIP);

        if (file_exists(APPROOT . "/resources/views/" . str_replace(".", "/", $view) . ".php")) {
            require_once APPROOT . "/resources/views/" . str_replace(".", "/", $view) . ".php";
        } else {
            $error = new ErrorPage();
            $error->_404_();
        }
    }
}
