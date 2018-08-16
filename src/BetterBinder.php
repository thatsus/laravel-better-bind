<?php

namespace ThatsUs;

class BetterBinder
{
    private $ignore_parameters = [];

    public function ignoreParameters(array $params = [])
    {
        $this->ignore_parameters = $params;
        return $this;
    }

    public function getIgnoreParameters()
    {
        return $this->ignore_parameters;
    }
}
