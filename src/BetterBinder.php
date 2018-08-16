<?php

namespace ThatsUs;

class BetterBinder
{
    private $ignore_parameters = [];

    public function ignoreParameters(...$params)
    {
        if (is_array($params[0])) {
            $params = $params[0];
        }
        $this->ignore_parameters = $params;
        return $this;
    }

    public function getIgnoreParameters()
    {
        return $this->ignore_parameters;
    }
}
