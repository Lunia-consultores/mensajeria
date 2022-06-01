<?php

namespace Mensajeria\Domain\Model\Mensajes;

class Payload
{
    /**
     * @var string
     */
    public $tipo;
    /**
     * @var array|string
     */
    public $data;

    /**
     * @param TipoMensaje $tipo
     * @param $data
     */
    public function __construct(TipoMensaje $tipo, $data)
    {
        $this->tipo = $tipo;
        $this->data = $data;
    }

    public function __toString(): string
    {
        return json_encode(
            [
                'tipo' => (string)$this->tipo,
                'data' => $this->data
            ]);
    }
}
