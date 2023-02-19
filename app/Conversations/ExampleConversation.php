<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class ExampleConversation extends Conversation
{
    /**
     * First question
     */
    protected $house_number;
    public function askReason()
    {
        $test = 'wuau';
        $this->say('Guau' . $test);
        /*
        $question = Question::create("Huh - you woke me up. What do you need?")
            ->fallback('Unable to ask question')
            ->callbackId('ask_reason')
            ->addButtons([
                Button::create('Tell a joke')->value('joke'),
                Button::create('Give me a fancy quote')->value('quote'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'joke') {
                    $joke = json_decode(file_get_contents('http://api.icndb.com/jokes/random'));
                    $this->say($joke->value->joke);
                } else {
                    $this->say(Inspiring::quote());
                }
            }
        });
        */
    }

    public function howMuchDoIOwe()
    {
        $this->ask('Hola vecino, ¿De que casa es?, por favor escriba solo el numero', function(Answer $answer) {
            // Save result


            $house_number = $answer->getText();

            $this->say('Vecino de la casa '.$house_number. ' usted esta al corriente');
        });
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->howMuchDoIOwe();
    }
}
