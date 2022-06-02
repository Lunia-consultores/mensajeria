<?php

namespace Mensajeria\Domain\Model\Colas;

use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Mensajes\Manejador;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\MensajeEnManejadorEquivocadoException;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\RoutingKey;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajes;
use Mensajeria\Domain\Service\Mensajes\ProcesaRespuesta;
use OutOfBoundsException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 *
 */
class Cola
{
    /**
     * @var string
     */
    private string $nombre = '';
    /**
     * @var Conexion
     */
    private Conexion $conexion;
    /**
     * @var bool
     */
    private bool $exclusiva = false;
    /**
     * @var Manejador[]
     */
    private array $manejadores;

    /**
     * @param string $nombre
     * @param Conexion $conexion
     * @param bool $exclusiva
     * @param Manejador[] $manejadores
     */
    public function __construct(string $nombre, Conexion $conexion, bool $exclusiva = false, array $manejadores = [])
    {
        $this->nombre = $nombre;
        $this->conexion = $conexion;
        $this->exclusiva = $exclusiva;
        $this->manejadores = $manejadores;
        $this->declarar();
    }

    private function declarar(): void
    {
        $this->conexion->canal()->queue_declare($this->nombre, false, true, $this->exclusiva, false);
    }

    public function consumir(Etiqueta $etiqueta = null,bool $encolarDeVuelta = true)
    {
        $this->conexion->canal()->basic_consume($this->nombre, (string)$etiqueta, false, false, false, false, function (AMQPMessage $req) use ($encolarDeVuelta){
            $result = null;

            $request = json_decode($req->body, true);

            $manejador = $this->esMensajeParaMi($req);

            if ($manejador === false) {
                $req->nack($encolarDeVuelta);
                return;
            }

            $payloadRecibida = new Payload(new TipoMensaje($request['tipo']), $request['data']);

            try {
                $result = (new ProcesaRespuesta())->execute($payloadRecibida, $manejador);
            } catch (MensajeEnManejadorEquivocadoException $manejadorEquivocadoException) {
                $req->nack();
            }

            try {
                $replyTo = $req->get('reply_to');
                $correlationId = $req->get('correlation_id');
                (new NotificarMensajes($this->conexion))->execute(
                    new Mensaje(new RoutingKey($replyTo), new Payload(new TipoMensaje('respuesta-sincrona'),$result), false, $correlationId, $replyTo,true)
                );

            } catch (OutOfBoundsException $e) {
                $req->ack();
                return;
            }

            $req->ack();
        });

    }

    /**
     * @param AMQPMessage $AMQPMessage
     * @return false|Manejador
     */
    private function esMensajeParaMi(AMQPMessage $AMQPMessage)
    {
        $request = json_decode($AMQPMessage->body, true);

        if (!isset($request['tipo'])) {
            return false;
        }

        foreach ($this->manejadores as $manejador) {
            foreach ($manejador->tiposPermitidos() as $tipoValido) {
                if ($tipoValido->compare(new TipoMensaje($request['tipo']))) {
                    return $manejador;
                }
            }
        }

        return false;
    }
}