<?php

namespace Tests\Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Mensajes\Manejador;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\MensajeEnManejadorEquivocadoException;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\RoutingKey;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Mensajes\ProcesaRespuesta;
use PHPUnit\Framework\TestCase;

class ProcesaMensajesTest extends TestCase
{
    public function testSiElMensajeNoEsMioPasoDeEl(){

        $this->expectException(MensajeEnManejadorEquivocadoException::class);

        $mensaje = new Payload(new TipoMensaje('tipo-uno'),[]);

        $tipos = [new TipoMensaje('dos'),new TipoMensaje('tres')];

        $procesaRespuesta = (new ProcesaRespuesta())->execute($mensaje, new Manejador($tipos, function (){
            return 'respuesta valida';
        }));

        $this->assertNotEquals('respuesta valida', $procesaRespuesta);
    }

    public function testDevuelveLoDelCallBackSiEsMiTipo(){

        $mensaje = new Payload(new TipoMensaje('tipo-uno'),[]);

        $tipos = [new TipoMensaje('tipo-uno'), new TipoMensaje('dos'),new TipoMensaje('tres')];

        $procesaRespuesta = (new ProcesaRespuesta())->execute($mensaje, new Manejador($tipos, function (){
            return 'respuesta valida';
        }));

        $this->assertEquals('respuesta valida', $procesaRespuesta);
    }


    public function testElCallBackUsaElContenidoDelMensaje(){

        $mensaje = new Payload(new TipoMensaje('tipo-uno'),['fecha' => '2020-01-01']);

        $tipos = [new TipoMensaje('tipo-uno'), new TipoMensaje('dos'),new TipoMensaje('tres')];

        $procesaRespuesta = (new ProcesaRespuesta())->execute($mensaje, new Manejador($tipos, function ($mensaje){
            return $mensaje->data['fecha'];
        }));

        $this->assertEquals($mensaje->data['fecha'], $procesaRespuesta);
    }


}
