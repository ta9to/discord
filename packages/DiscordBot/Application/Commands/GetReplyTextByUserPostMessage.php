<?php

namespace DiscordBot\Application\Commands;


use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\Voice\VoiceClient;
use function Sodium\crypto_secretbox;

class GetReplyTextByUserPostMessage
{
    /** @var Discord */
    private $discord;

    /** @var Message */
    private $message;

    /** @var string */
    private $command;

    /** @var string */
    private $q;

    const COMMANDS = [
        'opgg'  => 'getUrl',
        'blitz' => 'getUrl',
        'チーム分け' => 'teamDiv',
        'チーム分け2' => 'teamDiv2',
        'チーム' => 'teamDiv2',
        'ちーむ' => 'teamDiv2',
        'team' => 'teamDiv2',
        'mh' => 'opggCapture',
        'summoner' => 'replySummoner',
        'matchlist' => 'replyMatchList',
    ];

    public function __invoke(Discord $discord, Message $message)
    {
        $this->discord = $discord;
        $this->message = $message;
        $tmp = preg_split('/[\s|\x{3000}]+/u', $message->content);
        $this->command = $tmp[0];
        $this->q = $tmp[1] ?? null;
        $func = self::COMMANDS[$this->command] ?? null;
        if ($func) {
            return $this->$func();
        }
    }

    private function replyMatchList():string
    {
        $riotApiKey = $_ENV['RIOT_API_KEY'];
        $summonerJson = json_decode($this->_getSummoner());
        $matchListUrl = "https://asia.api.riotgames.com/lol/match/v5/matches/by-puuid/{$summonerJson->puuid}/ids?endIndex=5&beginIndex=0&api_key={$riotApiKey}";
        $matchListRaw = file_get_contents($matchListUrl);
        return $matchListRaw;
    }

    private function replySummoner():string
    {
        $summonerRaw = $this->_getSummoner();
        return $summonerRaw;
    }

    private function _getSummoner():string
    {
        $riotApiKey = $_ENV['RIOT_API_KEY'];
        $summoerUrl = "https://jp1.api.riotgames.com/lol/summoner/v4/summoners/by-name/{$this->q}?api_key={$riotApiKey}";
        $summonerRaw = file_get_contents($summoerUrl);
        return $summonerRaw;
    }

    private function opggCapture():string
    {
        $this->message->reply('during capture...');
        $url = "https://jp.op.gg/summoner/userName={$this->q}";
        $ss = "http://api.screenshotlayer.com/api/capture?access_key=e5b9b9dafff24a052cc9d62f930f2d2c&url={$url}&viewport=1100x1000&fullpage=1&ttl=1200";
        file_get_contents($ss);
        sleep(3);
        return $ss;
    }

    private function teamDiv2()
    {
        $array_divide = function ($arr, $division) {
            $count = ceil(count($arr) / $division); // 部分配列1個あたりの要素数
            $ret = array_chunk($arr, $count);

            return $ret;
        };
        $roles = ['top', 'mid', 'jg', 'bot', 'sup'];
        shuffle($roles);
        /** @var Channel $channel */
        foreach ($this->message->channel->guild->channels as $channel) {
            if ($channel->members->count() >= 1) {
                $users = [];
                /** @var VoiceStateUpdate $member */
                foreach ($channel->members as $member) {
                    /** @var Member $user */
                    $user = $this->message->channel->guild->members->get('id', $member->user_id);
                    array_push($users, $user->username);
                }
                shuffle($users);
                $divide = $array_divide($users, 2);
                foreach ($divide as $i => $users) {
                    $_users = array_map(function($name, $i) use ($roles) {
                        $role = $roles[$i] ?? '';
                        return "{$name}($role)";
                    }, $users, range(1, count($users)));
                    $embed = [
                        'color' => $i === 0 ? '16711680' : '255',
                        'title' => $i === 0 ? 'レッドチーム' : 'ブルーチーム',
                        'description' => implode(' ', $_users),
                    ];
                    $this->message->channel->sendMessage('', false, $embed);
                }
            }
        }
    }

    private function teamDiv():string
    {
        $num = (int)mb_convert_kana($this->q, "n");
        if ($num > 10) {
            return "10以下を指定してください";
        } elseif ($num <= 2) {
            return "3以上を指定してください";
        } else {
            $text = "\nAチーム\n";
            $memberIdArray = range(1,$num);
            shuffle($memberIdArray);
            $n=0;
            foreach ($memberIdArray as $val) {
                if ($n === (int)floor(count($memberIdArray)/2)) {
                    $text .= "\n\nBチーム\n";
                }
                $text .= "{$val} ";
                $n++;
            }
            return $text;
        }
    }

    /**
     * シンプルにURL返すだけで良いやつはこれで
     * @return string
     */
    private function getUrl():string
    {
        switch ($this->command) {
            case 'opgg':
                $urlFormat = "https://jp.op.gg/summoner/userName=%s";
                break;
            case 'blitz':
                $urlFormat = "https://blitz.gg/lol/profile/jp1/%s";
                break;
        }
        return sprintf($urlFormat, $this->q);
    }
}