<?php

class INeedParamsWithTypes
{
    public function __construct(
        stdClass $first_param, 
        string $second_param,
        string $third_param = 'default'
    )
    {
    }
}
