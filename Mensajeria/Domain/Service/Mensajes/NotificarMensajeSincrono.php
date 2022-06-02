<?php

namespace Mensajeria\Domain\Service\Mensajes;

use Exception;
use Illuminate\Support\Facades\Log;
use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Conexion\InicializaConexion;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

class NotificarMensajeSincrono
{
    private Conexion $conexion;

    public function __construct(Conexion $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * @param Mensaje $mensaje
     * @return Payload | null
     */
    public function execute(Mensaje $mensaje)
    {
        $respuesta = null;

        $this->conexion->canal()->queue_declare($mensaje->replyTo(),  false, true, false, true);
        $this->conexion->canal()->queue_declare($mensaje->routingKey(), false, true, false, false);

        $msg = new AMQPMessage((string)$mensaje->payload(),
            [
                'correlation_id' => $mensaje->correlationId(),
                'reply_to' => $mensaje->replyTo()
            ]
        );
        $this->conexion->canal()->basic_publish($msg, $this->conexion->datosConexion()->exchange(), $mensaje->routingKey());
        $this->conexion->canal()->basic_consume($mensaje->replyTo(), '', false, true, false, false, function (AMQPMessage $response) use (&$respuesta, $mensaje) {
            if ($response->get('correlation_id') == $mensaje->correlationId()) {
                $msgRespuesta = json_decode($response->body, true);
                $respuesta = new Payload(new TipoMensaje($msgRespuesta['tipo']),$msgRespuesta['data']);
            }
        });
        try {
            $this->conexion->canal()->wait(null, false, 5);
        } catch (AMQPTimeoutException $e) {
        }

        return $respuesta;
    }



    public function conexion()
    {
        return $this->conexion;
    }
}