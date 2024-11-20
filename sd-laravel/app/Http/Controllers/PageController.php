<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PageController extends Controller
{

    public function home()  {

        echo "Hello, Home Page Aerones";

        return view("home.index");
    }

}
