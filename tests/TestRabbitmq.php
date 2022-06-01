<?php

namespace Tests;

use GuzzleHttp\Client;
use Squeezely\RabbitMQ\Management\Configuration\AbstractConfiguration;
use Squeezely\RabbitMQ\Management\Configuration\ArrayConfiguration;
use Squeezely\RabbitMQ\Management\Queue\QueueService;

trait TestRabbitmq
{
    protected function initQueue(): void {

        $config = new AbstractConfiguration('127.0.0.1', 15672, 'http', 'guest', 'guest');
        $config = new ArrayConfiguration([
            'hostname' => '127.0.0.1',
            'port' => 15672,
            'protocol' => 'http',
            'username' => 'guest',
            'password' => 'guest'
        ]);

        $queueService = new QueueService($config);

        $queueService->createQueue(
            'tipo:mensaje-uno',
            '%2F',
            [
                'passive' => false,
                'durable' => true,
                'exclusive' => false,
                'auto_delete' => false,
            ]
        );
    }

    protected function obtenerMensajes(string $cola, int $numero = 1,string $vhost = '/', bool $purgar = true){

        $client = new Client();
        $response = $client->post('http://127.0.0.1:15672/api/queues/'.urlencode($vhost).'/'.urlencode($cola).'/get', [
            'auth' => [
                'guest',
                'guest'
            ],
            'headers' => [
                'content-type' => 'application/json'
            ],
            'json' => [
                'vhost' => $vhost,
                'name' => $cola,
                'truncate' => '50000',
                'ackmode' => 'ack_requeue_true',
                'encoding' => 'auto',
                'count' => $numero,
            ]
        ]);

        if($purgar) {
            $this->purgarCola($cola,$vhost);
        }

        return $this->parseaMensajes($response);
    }

    protected function purgarCola(string $cola, string $vhost) {

        $client = new Client();
        $client->delete('http://127.0.0.1:15672/api/queues/'.urlencode($vhost).'/'.urlencode($cola).'/contents', [
            'auth' => [
                'guest',
                'guest'
            ],
            'headers' => [
                'content-type' => 'application/json'
            ],
            'json' => [
                'vhost' => $vhost,
                'name' => $cola,
                'mode' => 'purge',
            ]
        ]);
    }
}