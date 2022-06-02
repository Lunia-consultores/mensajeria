<?php

namespace Tests\Mensajeria\Domain\Model\Colas;

use http\Exception\InvalidArgumentException;
use Mensajeria\Domain\Model\Colas\Cola;
use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use Mensajeria\Domain\Model\Mensajes\Manejador;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\RoutingKey;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Conexion\InicializaConexion;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajes;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajeSincrono;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit\Framework\TestCase;
use Tests\TestRabbitmq;

class ColaTest extends TestCase
{
    use TestRabbitmq;
    private Conexion $conexion;
    private Conexion $conexion2;
    private DatosConexion $datosConexion;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->datosConexion = new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', '');

        $this->conexion = (new InicializaConexion())->execute($this->datosConexion);
        $this->conexion2 = (new InicializaConexion())->execute($this->datosConexion);

        $this->initConnection();

    }

    public function testDebeDeclararLaCola(){

        $uuid = uniqid();

        $cola = new Cola($uuid,$this->conexion, false,[]);

        $this->assertNotFalse($cola);
    }

    public function testDebeEjecutarElManejador(){

        $resultado = 1;

        $uuid = uniqid();

        $manejador = new Manejador([new TipoMensaje('mensaje-uno')], function(Payload $payload) use (&$resultado){

            $resultado = (new ApplicationServicePrueba)->handle(
                new ApplicationServicePruebaRequest(
                    $payload->data['id']
                )
            );

        });

        $cola = new Cola($uuid,$this->conexion, false,[$manejador]);

        $notificar = new NotificarMensajes($this->conexion);

        $notificar->execute(
            [
                new Mensaje(new RoutingKey($uuid),
                    new Payload(
                        new TipoMensaje('mensaje-uno'),
                        [
                            'id' => 27
                        ]
                    )
                ),]);

        $cola->consumir();

        try{
            $this->conexion->loopHastaQueVacia();
        }catch (AMQPTimeoutException $AMQPTimeoutException){

        }
        $this->conexion->cerrar();
        $this->assertEquals(27,$resultado);
    }


    public function testDebeDevolverLaRespuestaEnCasoDeMensajeSincrono(){

        $correlationId = 45;
        $replyTo = uniqid();

        $manejador = new Manejador([new TipoMensaje('cancelar-solicitud-plaza')], function(Payload $payload) use (&$resultado){
            $resultado = (new ApplicationServicePrueba)->handle(
                new ApplicationServicePruebaRequest(
                    $payload->data['persona_uuid']
                )
            );
            return $resultado;
        });

        $uuid = uniqid();

        $cola = new Cola($uuid,$this->conexion2, false,[$manejador]);

        $payloadPregunta = new Payload(new TipoMensaje('cancelar-solicitud-plaza'), [
            'persona_uuid' => uniqid(),
            'fecha' => date('Y-m-d'),
        ]);

        $mensajeInicial = new Mensaje(new RoutingKey($uuid), $payloadPregunta, true,$correlationId, $replyTo);

        (new NotificarMensajeSincrono($this->conexion))->execute($mensajeInicial);

        $cola->consumir();

        try{
            $this->conexion2->loopHastaQueVacia();
        }catch (AMQPTimeoutException $AMQPTimeoutException){

        }
        $this->conexion->cerrar();
        $this->conexion2->cerrar();
    }

    public function testNoDebeEjecutarElManejador(){

        $resultado = 1;

        $uuid = uniqid();

        $manejador = new Manejador([new TipoMensaje('mensaje-dos')], function(Payload $payload) use (&$resultado){

            $resultado = (new ApplicationServicePrueba)->handle(
                new ApplicationServicePruebaRequest(
                    $payload->data['id']
                )
            );

        });

        $cola = new Cola($uuid,$this->conexion, false,[$manejador]);

        $notificar = new NotificarMensajes($this->conexion);

        $notificar->execute(
            [
                new Mensaje(new RoutingKey($uuid),
                    new Payload(
                        new TipoMensaje('mensaje-uno'),
                        [
                            'id' => 27
                        ]
                    )
                ),]);

        $cola->consumir(null,false);

        try{
            $this->conexion->loopHastaQueVacia();
        }catch (AMQPTimeoutException $AMQPTimeoutException){

        }
        $this->conexion->cerrar();
        $this->assertEquals(1,$resultado);
    }
}