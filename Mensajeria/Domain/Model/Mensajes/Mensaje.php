<?php
/**
 * Fichero Propiedad de LUNIA Consultores.
 * Desarrollador: Juan Francisco Sánchez Aldeguer
 * Date: 17/5/22
 * Time: 16:43
 */

namespace Mensajeria\Domain\Model\Mensajes;

class Mensaje
{
    /**
     * @var RoutingKey
     */
    private $routingKey;
    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var string
     */
    private $correlationId;
    /**
     * @var string
     */
    private $replyTo;
    private bool $sincrono;
    private bool $respuesta;

    public function __construct(RoutingKey $routingKey, Payload $payload, bool $sincrono = false,$correlationId = null,$replyTo = null,bool $respuesta = false)
    {
        $this->routingKey = $routingKey;
        $this->payload = $payload;
        $this->correlationId = $correlationId ?? uniqid('', true);
        $this->replyTo = $replyTo ?? uniqid('queue_', true);
        $this->sincrono = $sincrono;
        $this->respuesta = $respuesta;
    }

    /**
     * @return string
     */
    public function correlationId(): string
    {
        return $this->correlationId;
    }

    /**
     * @return string
     */
    public function replyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @return RoutingKey
     */
    public function routingKey(): RoutingKey
    {
        return $this->routingKey;
    }

    /**
     * @return Payload
     */
    public function payload(): Payload
    {
        return $this->payload;
    }

    /**
     * @return bool
     */
    public function esSincrono(): bool
    {
        return $this->sincrono;
    }
    /**
     * @return bool
     */
    public function esRespuesta(): bool
    {
        return $this->respuesta;
    }

    public function compararContenido(Mensaje $otroMensaje): bool {
        return ($this->payload === $otroMensaje->payload) &&
            ($this->routingKey === $otroMensaje->routingKey) &&
            ($this->esSincrono() === $otroMensaje->esSincrono()) &&
            ($this->esRespuesta() === $otroMensaje->esRespuesta());
    }


}
