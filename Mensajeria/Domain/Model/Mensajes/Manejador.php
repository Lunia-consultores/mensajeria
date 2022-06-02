<?php

namespace Mensajeria\Domain\Model\Mensajes;

/**
 *
 */
class Manejador
{
    /**
     * @var TipoMensaje[]
     */
    private array $tiposPermitidos;
    /**
     * @var \Closure
     */
    private \Closure $servicio;

    /**
     * @param array $tiposPermitidos
     * @param \Closure $servicio
     */
    public function __construct(array $tiposPermitidos, \Closure $servicio)
    {
        $this->tiposPermitidos = $tiposPermitidos;
        $this->servicio = $servicio;
    }

    /**
     * @return TipoMensaje[]
     */
    public function tiposPermitidos(): array
    {
        return $this->tiposPermitidos;
    }

    /**
     * @return \Closure
     */
    public function servicio(): \Closure
    {
        return $this->servicio;
    }

}