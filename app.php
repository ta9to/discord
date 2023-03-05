<?php
include __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use DiscordBot\Chimney;
use Discord\Parts\Channel\Message;
use Discord\Discord;
use Discord\WebSockets\Intents;
use DiscordBot\ChimneyServices\Team;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'intents' => Intents::getDefaultIntents(),
]);

$discord->on('ready', function (Discord $discord) {
    $discord->on('message', function (Message $message) {
        $chimney = new Chimney(
            $message->author->username,
            $message->content,
            $message->mentions,
        );
        if ($chimney->hasReply()) {
            if ($chimney->service instanceof Team) {
                $chimney->service
                    ->setMessage($message)
                    ->execute();
            } else {
                $message->reply($chimney->message());
            }
        }
    });
});

$discord->run();