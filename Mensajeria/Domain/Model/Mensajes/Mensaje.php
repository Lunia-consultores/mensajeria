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
     * @var string
     */
    public $routingKey;
    /**
     * @var Payload
     */
    public $payload;

    public function __construct(string $routingKey, Payload $payload)
    {
        $this->routingKey = $routingKey;
        $this->payload = $payload;
    }

}
