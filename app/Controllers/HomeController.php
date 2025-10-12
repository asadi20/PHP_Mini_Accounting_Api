<?php
namespace app\Controllers;

class HomeController
{
    public function index()
    {
        echo json_encode("test Controller");
        return null;
    }
}