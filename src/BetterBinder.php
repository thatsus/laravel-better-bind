<?php

namespace ThatsUs;

class BetterBinder
{
    private $ignored_parameters = [];

    public function ignoreParameters(...$params)
    {
        if (is_array($params[0])) {
            $params = $params[0];
        }
        $this->ignored_parameters = $params;
        return $this;
    }

    public function getIgnoredParameters()
    {
        return $this->ignored_parameters;
    }
}
