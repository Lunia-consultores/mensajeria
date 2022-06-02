<?php

namespace Tests\Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Colas\Cola;
use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\RoutingKey;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Conexion\InicializaConexion;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajes;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajeSincrono;
use PhpAmqpLib\Exception\AMQPIOException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use Tests\TestRabbitmq;

/**
 *
 */
class NotificarMensajeSincronoTest extends TestCase
{

    use TestRabbitmq;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->initQueue();
    }


    /**
     * @return void
     */
    public function testShouldCallConexionWithParametersSpecified()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajeSincrono($conexion);

        $this->assertEquals(Conexion::class, get_class($notificar->conexion()));

    }

    /**
     * @return void
     */
    public function testExpectExceptionOnIncorrect()
    {

        $this->expectException(AMQPIOException::class);

        $conexion = (new InicializaConexion())->execute(new DatosConexion('127..0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

    }

    /**
     * @return void
     */
    public function testDebeMandarMensajesPasadosPorParametros()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajeSincrono($conexion);

        $notificar->execute(
            new Mensaje(new RoutingKey('tipo:mensaje-uno'),
                new Payload(
                    new TipoMensaje('mensaje-prueba'),
                    [
                        'id' => 27
                    ]
                )
            )
        );

        $mensajes = $this->obtenerMensajes('tipo:mensaje-uno', 1);

        $this->assertCount(1, $mensajes);

    }


    /**
     * @return void
     */
    public function testDebeMandarMensajeConElContenidoCorrect()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajeSincrono($conexion);

        $notificar->execute(
            new Mensaje(new RoutingKey('tipo:mensaje-uno'),
                new Payload(
                    new TipoMensaje('mensaje-prueba'),
                    [
                        'id' => 27
                    ]
                )
            )
        );

        $mensajes = $this->obtenerMensajes('tipo:mensaje-uno', 2);

        $this->assertEquals((string)new TipoMensaje('mensaje-prueba'), $mensajes[0]['tipo']);
        $this->assertEquals(['id' => 27], $mensajes[0]['data']);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testDebeObtenerLaRespuestaSiLaHubiera()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajeSincrono($conexion);

        $mensaje = new Mensaje(new RoutingKey('tipo:mensaje-uno'),
            new Payload(
                new TipoMensaje('mensaje-prueba'),
                [
                    'id' => 27
                ]
            )
        );

        $conexion->canal()->queue_declare($mensaje->replyTo(),  false, true, false, true);

        $notificarRespuesta = new NotificarMensajes($conexion);
        $payloadRespuesta = new Payload(new TipoMensaje('tipo-respuesta'), ['informacion_importante' => 45]);

        $notificarRespuesta->execute([new Mensaje(new RoutingKey($mensaje->replyTo()), $payloadRespuesta,false,$mensaje->correlationId(),$mensaje->replyTo(),true)]);

        $respuesta = $notificar->execute($mensaje);

        $this->assertEquals($payloadRespuesta, $respuesta);
    }


}
