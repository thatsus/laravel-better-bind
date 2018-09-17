<?php


class INeedParamsWithAllTheTypes
{
    public $my_stdClass;
    public $my_self;
    public $my_array;
    public $my_callable;
    public $my_bool;
    public $my_float;
    public $my_int;
    public $my_string;

    public function __construct(
        stdClass $my_stdClass = null,
        self     $my_self     = null,
        array    $my_array    = null,
        callable $my_callable = null,
        bool     $my_bool     = null,
        float    $my_float    = null,
        int      $my_int      = null,
        string   $my_string   = null
        /*
         Add more as they become available in PHP 7.1, 7.2, etc
         See: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
         */
    )
    {
        $this->my_stdClass = $my_stdClass;
        $this->my_self     = $my_self;
        $this->my_array    = $my_array;
        $this->my_callable = $my_callable;
        $this->my_bool     = $my_bool;
        $this->my_float    = $my_float;
        $this->my_int      = $my_int;
        $this->my_string   = $my_string;
    }
}
