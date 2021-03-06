<?php

namespace Tests\Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Conexion\Conexion;
use Mensajeria\Domain\Model\Conexion\DatosConexion;
use Mensajeria\Domain\Model\Mensajes\Mensaje;
use Mensajeria\Domain\Model\Mensajes\Payload;
use Mensajeria\Domain\Model\Mensajes\RoutingKey;
use Mensajeria\Domain\Model\Mensajes\TipoMensaje;
use Mensajeria\Domain\Service\Conexion\InicializaConexion;
use Mensajeria\Domain\Service\Mensajes\NotificarMensajes;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use Tests\TestRabbitmq;

class NotificarMensajesTest extends TestCase
{
    use TestRabbitmq;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->initQueue();


    }


    public function testShouldCallConexionWithParametersSpecified()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

        $this->assertEquals(Conexion::class, get_class($notificar->conexion()));

    }

    public function testExpectExceptionOnIncorrect()
    {
        $this->expectException(AMQPIOException::class);

        $conexion = (new InicializaConexion())->execute(new DatosConexion('127..0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

    }

    public function testDebeMandarMensajesPasadosPorParametros()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

        $notificar->execute(
            [
                new Mensaje(new RoutingKey('tipo:mensaje-uno'),
                    new Payload(
                        new TipoMensaje('mensaje-prueba'),
                        [
                            'id' => 27
                        ]
                    )
                ),
                new Mensaje(new RoutingKey('tipo:mensaje-uno'),
                    new Payload(
                        new TipoMensaje('mensaje-prueba'),
                        [
                            'id' => 27
                        ]
                    )
                )
            ]
        );

        $mensajes = $this->obtenerMensajes('tipo:mensaje-uno', 2);

        $this->assertCount(2, $mensajes);

    }

    public function testDebePermitirMensajesIndividuales()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

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


    public function testDebeMandarMensajeConElContenidoCorrect()
    {
        $conexion = (new InicializaConexion())->execute(new DatosConexion('127.0.0.1', 5672, 'guest', 'guest', '/', ''));

        $notificar = new NotificarMensajes($conexion);

        $notificar->execute(
            [
                new Mensaje(new RoutingKey('tipo:mensaje-uno'),
                    new Payload(
                        new TipoMensaje('mensaje-prueba'),
                        [
                            'id' => 27
                        ]
                    )
                )
            ]
        );

        $mensajes = $this->obtenerMensajes('tipo:mensaje-uno', 2);

        $this->assertEquals((string)new TipoMensaje('mensaje-prueba'), $mensajes[0]['tipo']);
        $this->assertEquals(['id' => 27], $mensajes[0]['data']);
    }



}
