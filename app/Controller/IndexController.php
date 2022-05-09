<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        return "hello world";
    }
}