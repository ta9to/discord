<?php

namespace DiscordBot\ChimneyServices;

class Opgg
{
    protected const URL = 'https://jp.op.gg/summoner/userName=';

    public function __construct
    (
        private string $arg,
    ) {}

    public function execute():string
    {
        return static::URL . $this->arg;
    }
}