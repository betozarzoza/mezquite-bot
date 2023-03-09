<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Balance;

class TestConversation extends Conversation
{
    /**
     * First question
     */
    protected $house_number;

    public function test()
    {
       $user = $this->getUser();
       print_r($user);
       return $user;
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->test();
    }
}
