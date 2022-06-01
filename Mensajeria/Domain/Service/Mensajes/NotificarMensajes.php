<?php
namespace Mensajeria\Domain\Service\Mensajes;

use Mensajeria\Domain\Model\Conexion\Conexion;

/**
 *
 */
abstract class NotificarMensajes {

    /**
     * @var Conexion
     */
    protected $conexion;

    public function __construct(Conexion $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * @param Mensaje[] $mensajes
     * @return void
     */
    public abstract function execute(array $mensajes): void;
}