<?php

namespace Mensajeria\Domain\Model\Mensajes;

class TipoMensaje
{
    private string $tipo;

    /**
     * @param string $tipo
     */
    public function __construct(string $tipo)
    {
        $this->tipo = $tipo;
    }


    public function __toString()
    {
        return $this->tipo;
    }

    public function compare(TipoMensaje $otroTipo) {
        return $otroTipo->tipo === $this->tipo;
    }


}