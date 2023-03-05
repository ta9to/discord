<?php
include __DIR__.'/vendor/autoload.php';

use Discord\WebSockets\Event;
use Dotenv\Dotenv;
use DiscordBot\Application\Commands\GetReplyTextByUserPostMessage;
use Discord\Parts\Channel\Message;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function startsWith($haystack, $needle) {
    return str_starts_with($haystack, $needle);
}

$discord = new \Discord\Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'intents' => \Discord\WebSockets\Intents::getDefaultIntents(),
]);

$getReplyTextByUserPostMessage = new GetReplyTextByUserPostMessage();

$discord->on('ready', function (\Discord\Discord $discord) use($getReplyTextByUserPostMessage) {
    $discord->on('message', function (Message $message) use($discord, $getReplyTextByUserPostMessage) {
        if ($message->author->username === '直腸亭チムニー' || $message->channel_id === '732386754056683651') { return; }
        $text = ($getReplyTextByUserPostMessage)($discord, $message);
        if ($text) { $message->reply($text); }
    });
});

$discord->run();