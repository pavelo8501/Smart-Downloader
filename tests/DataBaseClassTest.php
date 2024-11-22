<?php

use PHPUnit\Framework\TestCase;
use SmartDownloader\Handlers;


class NewDataBaseClass    extends Handlers\DataClassBase {

    public int $id  = 0;
    public string $property2  = "property2";
    public string $property3  = "property3";


    public function __construct()
    {

        $inArray = ["id" => $this->id, "property2" =>  $this->property2, "property3" =>  $this->property3];
        parent::execute($inArray);
    }

}

class DataBaseClassTest extends TestCase
{

    function testIfReflectionCreatesMethod(){


       // $test->execute([$this->id, $this->property2, $this->property3]);



        $test = new NewDataBaseClass();



    }



}