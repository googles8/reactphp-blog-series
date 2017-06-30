<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new React\Dns\Resolver\Factory();
$resolver = $factory->createCached('8.8.8.8', $loop);
$factory = new React\Datagram\Factory($loop, $resolver);

$stdin = new \React\Stream\ReadableResourceStream(STDIN, $loop);
$stdout = new \React\Stream\WritableResourceStream(STDOUT, $loop);

$factory->createClient('localhost:1234')
    ->then(
        function (React\Datagram\Socket $client) use ($stdin, $stdout) {
            $stdin->on('data', function($data) use ($client) {
                $client->send($data);
            });

            $client->on('message', function($message) use ($stdout) {
                $stdout->write($message);
            });
        },
        function($error) {
            echo 'ERROR: ' . $error->getMessage() . PHP_EOL;
        });

$loop->run();