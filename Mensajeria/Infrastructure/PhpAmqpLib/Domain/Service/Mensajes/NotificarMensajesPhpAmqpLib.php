<?php
/**
 * Fichero Propiedad de LUNIA Consultores.
 * Desarrollador: Juan Francisco Sánchez Aldeguer
 * Date: 17/5/22
 * Time: 16:42
 */
namespace Mensajeria\Infrastructure\PhpAmqpLib\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajes;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 *
 */
class NotificarMensajesPhpAmqpLib extends NotificarMensajes
{
    /**
     * @var AMQPStreamConnection
     */
    private $rabbitmqConexion;
    /**
     * @var
     */
    private $rabbitChannel;

    /**
     * @param Mensaje[] $mensajes
     * @throws \Exception
     */
    public function execute(array $mensajes): void
    {
        $this->iniciaConexion();

        $this->rabbitChannel = $this->rabbitmqConexion()->channel();

        foreach ($mensajes as $mensaje) {
            $msg = new AMQPMessage((string)$mensaje->payload);
            $this->rabbitChannel()->basic_publish($msg, $this->conexion->exchange(), $mensaje->routingKey);
        }

        $this->rabbitChannel()->close();

        $this->rabbitmqConexion()->close();
    }

    /**
     * @return mixed
     */
    public function rabbitmqConexion()
    {
        return $this->rabbitmqConexion;
    }

    /**
     * @return mixed
     */
    public function rabbitChannel()
    {
        return $this->rabbitChannel;
    }

    /**
     * @return void
     */
    protected function iniciaConexion()
    {
        $this->rabbitmqConexion= new AMQPStreamConnection($this->conexion->host(),$this->conexion->puerto(),$this->conexion->usuario(), $this->conexion->clave(),$this->conexion->vhost());
    }
}
