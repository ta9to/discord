<?php

namespace DiscordBot\ChimneyServices;

use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\Parts\User\Member;

/**
 * Class Team
 * @todo refactor
 * @package DiscordBot\ChimneyServices
 * @property Message $message
 */
class Team
{
    /** @var Message */
    private $message;

    public function setMessage(Message $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function execute():string
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
                    array_push($users, $user->user->username);
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
        return '';
    }
}