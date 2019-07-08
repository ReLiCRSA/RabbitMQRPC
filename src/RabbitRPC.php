<?php

namespace ReLiCRSA;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitRPC
 * @package App\Libs
 */
class RabbitRPC
{
    private $connection;
    private $channel;
    private $corrolationId;
    private $callbackQueue;
    private $response;

    /**
     * RabbitRPC constructor.
     */
    public function __construct($rabbitHost, $rabitLogin, $rabbitPassword, $rabbitVHost = "/", $rabbitPort = 5672)
    {
        $this->connection = new AMQPStreamConnection($rabbitHost, $rabbitPort, $rabitLogin, $rabbitPassword, $rabbitVHost);
        $this->channel = $this->connection->channel();
    }

    /**
     * Instantiate the Server instance
     *
     * @param $callBack
     * @return $this
     */
    public function serverQueue($callBack, $serverQueue = 'rpc_queue')
    {
        $this->channel->queue_declare($serverQueue, false, false, false, false);
        $this->channel->basic_qos(null, 1, null);
        $onTriggerCall = function ($req) use ($callBack) {
            $response = call_user_func($callBack, $req);
            $msg = new AMQPMessage(
                json_encode($response),
                array('correlation_id' => $req->get('correlation_id'))
            );

            $req->delivery_info['channel']->basic_publish(
                $msg,
                '',
                $req->get('reply_to')
            );

            $req->delivery_info['channel']->basic_ack(
                $req->delivery_info['delivery_tag']
            );
        };
        $this->channel->basic_consume($serverQueue, '', false, false, false, false, $onTriggerCall);
        return $this;
    }

    /**
     * Start the listener process
     *
     * @throws \ErrorException
     */
    public function serverStart()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Start the client process
     *
     * @return $this
     */
    public function clientStart()
    {
        list($this->callbackQueue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );
        $this->channel->basic_consume(
            $this->callbackQueue,
            '',
            false,
            true,
            false,
            false,
            array(
                $this,
                'onResponse'
            )
        );
        return $this;
    }

    /**
     * Once response is received decode and return
     *
     * @param $response
     */
    public function onResponse($response)
    {
        if ($response->get('correlation_id') == $this->corrolationId) {
            if (!$this->response = json_decode($response->body, true)) {
                $this->response = $response->body;
            };
        }
    }

    /**
     * Request data from remote source and wait for response
     *
     * @param $thePayLoad
     * @param string $queueToUse
     * @return null
     * @throws \ErrorException
     */
    public function rpcClientRequest($thePayLoad, $queueToUse = 'rpc_queue')
    {
        $this->corrolationId = uniqid();
        $this->response = null;
        $msg = new AMQPMessage(
            json_encode($thePayLoad),
            array(
                'correlation_id' => $this->corrolationId,
                'reply_to' => $this->callbackQueue
            )
        );
        $this->channel->basic_publish($msg, '', $queueToUse);
        while (empty($this->response)) {
            $this->channel->wait();
        }
        return $this->response;
    }
}
