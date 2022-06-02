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

    private Conexion $conexion;

    public function __construct(Conexion $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * @param Mensaje[]|Mensaje $mensajes
     * @throws Exception
     */
    public function execute($mensajes): void
    {
        if(!is_array($mensajes)) {
            $mensajes = [$mensajes];
        }

        foreach ($mensajes as $mensaje) {
            if($mensaje->esRespuesta()){
                $msg = new AMQPMessage((string)$mensaje->payload(), [
                    'correlation_id' => $mensaje->correlationId(),
                    'reply_to' => $mensaje->replyTo()
                ]);

                $this->conexion->canal()->basic_publish($msg, $this->conexion->datosConexion()->exchange(), $mensaje->replyTo());

            }else{
                $msg = new AMQPMessage((string)$mensaje->payload());
                $this->conexion->canal()->basic_publish($msg, $this->conexion->datosConexion()->exchange(), $mensaje->routingKey());

            }
        }
    }

    public function conexion()
    {
        return $this->conexion;
    }
}