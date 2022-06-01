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

    public function __construct(string $tipo, $data)
    {
        $this->tipo = $tipo;
        $this->data = $data;
    }

    public function __toString(): string
    {
        return json_encode(
            [
                'tipo' => $this->tipo,
                'data' => $this->data
            ]);
    }
}
