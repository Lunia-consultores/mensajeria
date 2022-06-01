<?php

namespace Mensajeria\Domain\Model\Conexion;

/**
 *
 */
class DatosConexion
{
    /**
     * @var string
     */
    protected $host;
    /**
     * @var int
     */
    protected $puerto;
    /**
     * @var ?string
     */
    protected $usuario;
    /**
     * @var ?string
     */
    protected $clave;
    /**
     * @var string
     */
    protected $vhost;
    /**
     * @var string
     */
    protected $exchange;

    /**
     * @param string $host
     * @param int $puerto
     * @param string|null $usuario
     * @param string|null $clave
     * @param string $vhost
     * @param string $exchange
     */
    public function __construct(string $host, int $puerto, ?string $usuario, ?string $clave, string $vhost, string $exchange)
    {
        $this->host = $host;
        $this->puerto = $puerto;
        $this->usuario = $usuario;
        $this->clave = $clave;
        $this->vhost = $vhost;
        $this->exchange = $exchange;
    }

    public function exchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function host(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function puerto(): int
    {
        return $this->puerto;
    }

    /**
     * @return string|null
     */
    public function usuario(): ?string
    {
        return $this->usuario;
    }

    /**
     * @return string|null
     */
    public function clave(): ?string
    {
        return $this->clave;
    }

    /**
     * @return string
     */
    public function vhost(): string
    {
        return $this->vhost;
    }


}