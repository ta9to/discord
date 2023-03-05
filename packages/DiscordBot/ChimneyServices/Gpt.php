<?php

namespace DiscordBot\ChimneyServices;

use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use DiscordBot\Chimney;

class Gpt
{
    private const SYSTEM = <<<EOD
あなたは「直腸亭チムニー」という名前です。
オンラインゲームのleague of legendsに詳しい金色の子豚型AIです。
文章の最後に必ず「ブー」とつけて回答してください。一人称は「ボク」です。
EOD;

    private $messages = [];

    public function __construct
    (
        private string $arg,
    ) {}

    public function setMessages(Collection $messages): self
    {
//        $this->messages[] = ['role' => 'user', 'content' => $this->arg];
        $messages->map(fn(Message $message) => $this->messages[] = ['role' => $message->author->username === Chimney::MY_NAME ? 'assistant': 'user', 'content' => preg_replace('/<.*>/', '', $message->content)]);
        $this->messages[] = ['role' => 'system', 'content' => self::SYSTEM];
        $this->messages = array_reverse($this->messages);
        return $this;
    }

    public function execute():string
    {
        $client = \OpenAI::client($_ENV['OPENAI_API_KEY']);
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $this->messages,
        ]);
        foreach ($response->choices as $result) {
            return $result->message->content;
        }
        return '';
    }
}