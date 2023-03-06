<?php
include __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use DiscordBot\Chimney;
use Discord\Parts\Channel\Message;
use Discord\Discord;
use Discord\WebSockets\Intents;
use DiscordBot\ChimneyServices\Team;
use Discord\Helpers\Collection;
use DiscordBot\ChimneyServices\Gpt;
use Discord\Parts\Thread\Thread;
use Discord\WebSockets\Event;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\Voice\VoiceClient;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'intents' => Intents::getDefaultIntents(),
]);

$discord->on('ready', function (Discord $discord) {
    $discord->on(Event::MESSAGE_CREATE, function (Message $message) {
        // 本番環境ではテストチャンネルの投稿はスルー
        if ($_ENV['APP_ENV'] == 'production' && $message->channel_id == '732386754056683651') {
            return;
        }
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
                if ($chimney->service instanceof Gpt) {
                    $message->channel
                        ->getMessageHistory(['limit' => 5])
                        ->done(function (Collection $messages) use($chimney, $message) {
                            $chimney->service->setMessages($messages);
                            $message->reply($chimney->message());
//                            $res = $chimney->message();
//                            $thread = trim(preg_replace('/<.*>/', '', $chimney->input));
//                            $message
//                                ->startThread($thread)
//                                ->then(function (Thread $thread) use($res) {
//                                    $thread->sendMessage($res);
//                                });
                        });
                } else {
                    $message->reply($chimney->message());
                }
            }
        }
    });
});

$discord->run();