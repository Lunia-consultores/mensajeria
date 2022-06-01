<?php

namespace Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;

/**
 *
 */
class ProcesaRespuesta
{
    /**
     * @param TipoMensaje[] $tiposValidos
     * @param \Closure $callback
     */
    public function execute(Payload $payload, array $tiposValidos, \Closure $callback)
    {
        $esTipoValido = false;

        foreach ($tiposValidos as $tipoValido) {
            if ($tipoValido->compare($payload->tipo)) {
                $esTipoValido = true;
                break;
            }
        }

        if($esTipoValido === false) {
            return true;
        }

        return $callback($payload);

    }
}