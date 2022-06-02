<?php

namespace Mensajeria\Domain\Model\Colas;

class Etiqueta
{
    private string $etiqueta;

    /**
     * @param string $etiqueta
     */
    public function __construct(string $etiqueta)
    {
        $this->etiqueta = $etiqueta;
    }

    public function compare(Etiqueta $etiqueta): bool{
        return $this->etiqueta === $etiqueta->etiqueta();
    }

    /**
     * @return string
     */
    public function etiqueta(): string
    {
        return $this->etiqueta;
    }

    public function __toString(): string
    {
        return $this->etiqueta;
    }

}