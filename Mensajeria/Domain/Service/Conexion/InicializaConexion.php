<?php

namespace Mensajeria\Domain\Service\Conexion;

use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class InicializaConexion
{
    public function execute(DatosConexion $datosConexion): Conexion
    {
        $conexion = new AMQPStreamConnection($datosConexion->host(), $datosConexion->puerto(), $datosConexion->usuario(), $datosConexion->clave(), $datosConexion->vhost());

        return new Conexion($conexion, $conexion->channel(),$datosConexion);
    }
}