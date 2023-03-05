<?php
include __DIR__.'/vendor/autoload.php';

use Discord\WebSockets\Event;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function startsWith($haystack, $needle) {
    return str_starts_with($haystack, $needle);
}

$discord = new \Discord\Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'intents' => \Discord\WebSockets\Intents::getDefaultIntents(),
]);

$discord->on('ready', function (\Discord\Discord $discord) {
    echo "Bot is ready.", PHP_EOL;

    // Listen for events here
    $discord->on(Event::MESSAGE_CREATE, function (\Discord\Parts\Channel\Message $message) {
        echo "Recieved a message from {$message->author->username}: {$message->content}", PHP_EOL;
        if ('直腸亭チムニー' !== $message->author->username) {
            if (startsWith($message->content, 'build')) {
                $hoge = preg_split('/[\s|\x{3000}]+/u', $message->content);
                $q = $hoge[1];
                $url = "https://lolbuild.jp/build?q={$q}&champion=&is_top=1&type=build";
                $message->reply($url);
            } elseif (startsWith($message->content, 'opgg')) {
                $hoge = preg_split('/[\s|\x{3000}]+/u', $message->content);
                $q = $hoge[1];
                $url = "https://jp.op.gg/summoner/userName={$q}";
                $message->reply($url);
            } elseif (startsWith($message->content, 'mc-server')) {
                $hoge = preg_split('/[\s|\x{3000}]+/u', $message->content);
                $q = $hoge[1];
                $text = "minecraft server {$q}...";
                $message->reply($text);
            }
        }
    });
});

$discord->run();