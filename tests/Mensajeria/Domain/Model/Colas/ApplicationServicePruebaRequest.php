<?php

namespace Tests\Mensajeria\Domain\Model\Colas;

class ApplicationServicePruebaRequest
{
    public $parametroPrueba;

    /**
     * @param $parametroPrueba
     */
    public function __construct($parametroPrueba)
    {
        $this->parametroPrueba = $parametroPrueba;
    }
}