<?php

use PHPUnit\Framework\TestCase;
use DiscordBot\Chimney;

class ChimneyTest extends TestCase
{
    public function chimneyServiceProvider(): array
    {
        return [
            ['saisho', 'fuga', 'コマンドが存在しません'],
            ['ta9to', 'opgg ta9to', 'https://jp.op.gg/summoner/userName=ta9to'],
            ['ta9to', 'opgg', 'https://jp.op.gg/summoner/userName='],
            ['ta9to', 'blitz ta9to', 'https://blitz.gg/lol/profile/jp1/ta9to'],
        ];
    }

    /**
     * @dataProvider chimneyServiceProvider
     */
    public function testChimney($author, $input, $expected): void
    {
        $this->bot = new Chimney( $author, $input);
        if ($this->bot->hasReply()) {
            $this->assertEquals($expected, $this->bot->message());
        } else {
            $this->assertEquals('コマンドが存在しません', $this->bot->message());
        }
    }
}