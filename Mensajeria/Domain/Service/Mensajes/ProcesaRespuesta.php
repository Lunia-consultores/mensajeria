<?php

namespace Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Mensajes\Manejador;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\MensajeEnManejadorEquivocadoException;
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
    public function execute(Payload $payload, Manejador $manejador)
    {
        $esTipoValido = false;

        foreach ($manejador->tiposPermitidos() as $tipoValido) {
            if ($tipoValido->compare($payload->tipo)) {
                $esTipoValido = true;
                break;
            }
        }

        if($esTipoValido === false) {
            throw new MensajeEnManejadorEquivocadoException();
        }

        return $manejador->servicio()($payload);

    }
}