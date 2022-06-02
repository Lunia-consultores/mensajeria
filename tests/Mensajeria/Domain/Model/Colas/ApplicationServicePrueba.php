<?php

namespace Tests\Mensajeria\Domain\Model\Colas;

class ApplicationServicePrueba
{
    public function handle(ApplicationServicePruebaRequest $applicationServicePruebaRequest){
        return $applicationServicePruebaRequest->parametroPrueba;
    }
}