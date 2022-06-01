<?php
namespace Mensajeria\Domain\Service\Mensajes;

use Exception;
use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Service\Conexion\InicializaConexion;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 *
 */
class NotificarMensajes {

    /**
     * @var DatosConexion
     */
    protected $datosConexion;
    private Conexion $conexion;

    public function __construct(DatosConexion $conexion)
    {
        $this->datosConexion = $conexion;
    }

    /**
     * @param Mensaje[] $mensajes
     * @throws Exception
     */
    public function execute(array $mensajes): void
    {
        $this->iniciaConexion();

        foreach ($mensajes as $mensaje) {
            $msg = new AMQPMessage((string)$mensaje->payload);
            $this->conexion->canal()->basic_publish($msg, $this->datosConexion->exchange(), $mensaje->routingKey);
        }
        $this->conexion->cerrar();
    }


    /**
     * @return void
     */
    protected function iniciaConexion()
    {
        $this->conexion = (new InicializaConexion())->execute($this->datosConexion);
    }

    public function conexion()
    {
        return $this->conexion;
    }
}