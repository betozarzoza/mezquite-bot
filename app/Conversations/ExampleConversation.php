<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Balance;

class ExampleConversation extends Conversation
{
    /**
     * First question
     */
    protected $house_number;

    public function howMuchDoIOwe()
    {
        $this->ask('Hola vecino, ¿De que casa es?, por favor escriba solo el numero', function(Answer $answer) {
            // Save result


            $house_number = $answer->getText();
            $balance = Balance::where('house', intval($house_number))->first();

            if ($balance) {
                if ($balance->balance < 0) {
                   $this->say('Vecino de la casa '.$house_number. ' usted debe $' . abs($balance->balance));
                } else if ($balance->balance == 0) {
                    $this->say('Vecino de la casa '.$house_number. ' usted esta al corriente.');
                } else if ($balance->balance > 0) {
                    $this->say('Vecino de la casa '.$house_number. ' usted esta al corriente y hasta tiene un saldo a favor de $' . abs($balance->balance));
                }
                
            } else {
                 $this->say('No se encontro su casa, por favor ingrese solamente el numero');
            }
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
