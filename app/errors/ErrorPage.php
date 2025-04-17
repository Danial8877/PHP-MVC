<?php
class ErrorPage
{
    public function _403_()
    {
        http_response_code(403);
        die;
    }
    public function _404_()
    {
        http_response_code(404);
        die;
    }
    public function _500_()
    {
        http_response_code(500);
        die;
    }
}
