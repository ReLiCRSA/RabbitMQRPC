# RabbitMQRPC
To setup a minimum config instance you need the following

``$mqAgent = new RabbitRPC('localhost', 'guest', 'guest');``

This will start the class off with the connection string to the Rabbit MQ server. You will need these as minimum,

Optionally you can specify the Vhost and Port

``$mqAgent = new RabbitRPC('localhost', 'guest', 'guest', '/', 5672);``

## Server (Receiver)
The server side works on a call back, so this runs and subscribes to the queue specified and waits for incoming messages. This will grab those messages, 
which would lock them and not allow another process to grab them. Once completed the library will ACK the message which will close it off.

If the process crashes, the message is then released and another processor can pick it up to process. This means that a message is alway processed 
and never lost unless the RabbitMQ server is not setup to be persistant and clears on restart.

The server process will have a call back function so you can specify it as below

``$mqAgent->serverQueue('serverProcess');``

This will use a default queue name defined in the class, you can speficy the queue name as below.

``$mqAgent->serverQueue('serverProcess', 'my_special_queue');``

This will listen to that queue. This instance can only listen to one queue at a time

The `serverProcess` can just be a function as bellow

````
function serverProcess($request)
{
    // Get the body that was sent in
    $bodyOfRequest = $request->body;

    // Do some processing on the body and return an answer
    return $answer;
}
````

## Client
The client will send in a request to a queue and wait for a response. The response ill create another queue we subscribe to and delete it on receiving the answer.

The client needs the global setup and then you need to post the payload to the server

``$answer = $mqAgent->rpcClientRequest($payLoad);``

The payload will be JSON encoded and sent to the queue where it can then be used on the server side.

The answer from the sevrer will come back to the $answer variable and be able to get read. The above uses the default queue name, but you can specify the queue name as below.

``$answer = $mqAgent->rpcClientRequest($payLoad, 'queue_name');``

## Notes
* Client and server needs to both listen on the same queue
* This library does not create persistant queues on its own, you need to persist them
* This is prototype code used for proof of concept, hope this helps someone in the future
* There are examples in the `examples` directory with a docker config for you to try

