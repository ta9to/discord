<?php

namespace DiscordBot;

class Chimney
{
    private const MY_NAME = '直腸亭チムニー';

    public $service;

    public function __construct
    (
        private string $author,
        private string $input,
    ) {}

    public function isMyMessage(): bool
    {
        return $this->author === self::MY_NAME;
    }

    /**
     * 自分のメッセージかどうかを判定し、該当するサービスがあればインスタンスを生成する
     *
     * @return bool
     */
    public function hasReply(): bool
    {
        if ($this->isMyMessage() || !$this->setService()) {
            return false;
        }
        return true;
    }

    public function setService(): bool
    {
        @[$command, $arg] = preg_split('/[\s|\x{3000}]+/u', $this->input);
        $command = 'DiscordBot\ChimneyServices\\' . strtoupper($command);
        if (!class_exists($command)) {
            return false;
        }
        $this->service = new $command($arg ?? '');
        return true;
    }

    public function message():string
    {
        if ($this->service === null) {
            return 'コマンドが存在しません';
        }
        try {
            return $this->service->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}