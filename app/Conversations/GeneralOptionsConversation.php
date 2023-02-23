<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Movement;
use App\General;

class GeneralOptionsConversation extends Conversation
{
    /**
     * First question
     */

    public function showMovements()
    {
        $movements = Movement::latest()->limit(10)->get();
        foreach ($movements as $movement) {
          $this->say('Se '.$movement->type.' $'.$movement->amount.' el ' .$movement->created_at. ' por el concepto de '.$movement->description);
        }
    }

    public function showGeneralBalance()
    {
        $balance = General::where('name', 'balance')->first();
        $this->say('El saldo de el condominio es '.$balance->value);
    }

    public function showOptions()
    {
        $question = Question::create('Hola vecino, ¿En que le puedo ayudar?')
            ->fallback('No puedo ayudarle')
            ->callbackId('canthelp')
            ->addButtons([
                Button::create('Mostrar movimientos')->value('movements'),
                Button::create('Saldo del Condominio')->value('balance'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'gasto' or 'ingreso'
                if ($selectedValue == 'movements') {
                    $this->showMovements();
                } else if ($selectedValue == 'balance') {
                    $this->showGeneralBalance();
                }else {
                    $this->say('Opcion invalida.');
                }
            }
        });
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->showOptions();
    }
}
