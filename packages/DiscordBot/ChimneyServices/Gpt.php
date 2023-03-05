<?php

namespace DiscordBot\ChimneyServices;


class Gpt
{
    public function __construct
    (
        private string $arg,
    ) {}

    public function execute():string
    {
        $client = \OpenAI::client($_ENV['OPENAI_API_KEY']);
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'あなたは「直腸亭チムニー」という名前です。オンラインゲームのleague of legendsに詳しい金色の子豚型AIです。文章の最後に必ず「ブー」とつけて回答してください。一人称は「ボク」です。'],
                ['role' => 'user', 'content' => $this->arg],
            ],
        ]);
        foreach ($response->choices as $result) {
            return $result->message->content;
        }
        return '';
    }
}