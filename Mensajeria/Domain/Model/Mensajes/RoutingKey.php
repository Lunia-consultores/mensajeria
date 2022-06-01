<?php

namespace Mensajeria\Domain\Model\Mensajes;

class RoutingKey
{
    private string $routingKey;

    /**
     * @param string $routingKey
     */
    public function __construct(string $routingKey)
    {
        $this->routingKey = $routingKey;
    }


    public function __toString()
    {
        return $this->routingKey;
    }


}