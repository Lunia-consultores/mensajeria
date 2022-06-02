<?php

namespace Mensajeria\Domain\Model\Conexion;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPWriter;

/**
 *
 */
class Conexion
{
    /**
     * @var AMQPStreamConnection
     */
    private $conexionRabbit;
    /**
     * @var AMQPChannel
     */
    private $canal;

    /**
     * @param $conexionRabbit
     * @param $canal
     */
    public function __construct(AMQPStreamConnection $conexionRabbit, AMQPChannel $canal)
    {
        $this->conexionRabbit = $conexionRabbit;
        $this->canal = $canal;
        $this->canal->basic_qos(null, 1, null);
    }


    /**
     * @return AMQPChannel
     */
    public function canal(): AMQPChannel
    {
        return $this->canal;
    }

    public function cerrar(): void
    {
        $this->canal->close();
        $this->conexionRabbit->close();
    }

    public function loop(): void
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            while (true) {
                $this->sendHeartbeat();
                sleep(10);
            }
        } else {
            while ($this->canal->is_open()) {
                $this->canal->wait();
            }
        }
    }
    public function loopHastaQueVacia(): void
    {
        while (count($this->canal->callbacks)) {
            $this->canal->wait(null,false,2);
        }
    }

    private function sendHeartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $this->conexionRabbit->getIO()->write($pkt->getvalue());
    }
}