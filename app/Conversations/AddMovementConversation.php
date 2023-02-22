<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Movement;

class AddMovementConversation extends Conversation
{
    /**
     * First question
     */
    protected $house_number;

    protected $description;
    protected $amount;

    public function addExpense()
    {
        $this->ask('Ingrese el nombre de el gasto', function(Answer $answer) {
            $this->description = $answer->getText();
            $this->ask('Ingrese la cantidad de el gasto', function(Answer $answer) {
                $this->amount = $answer->getText();

                $movement = new Movement();
                $movement->description = $this->description;
                $movement->type = 'ingreso';
                $movement->amount = $this->amount;
                $movement->attatch = '';
                $movement->save();
            });
        });
    }

    public function addIncome()
    {
        $question = Question::create('Do you need a database?')
            ->fallback('Unable to create a new database')
            ->callbackId('create_database')
            ->addButtons([
                Button::create('Of course')->value('yes'),
                Button::create('Hell no!')->value('no'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'yes' or 'no'
                $selectedText = $answer->getText(); // will be either 'Of course' or 'Hell no!'
            }
        });
    }

    public function showOptions()
    {
        $question = Question::create('Hola vecino administrador, ¿En que le puedo ayudar?')
            ->fallback('No puedo ayudarle')
            ->callbackId('canthelp')
            ->addButtons([
                Button::create('Agregar gasto')->value('gasto'),
                Button::create('Agregar ingreso')->value('ingreso'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'gasto' or 'ingreso'
                if ($selectedValue == 'gasto') {
                    $this->addExpense();
                } else if ($selectedValue == 'ingreso') {
                    $this->addIncome();
                } else {
                    $this->say('Opcion invalida.');
                }
            }
        });
    }

    public function askPassword()
    {
         $this->ask('Ingrese contraseña de administrador', function(Answer $answer) {


            $password = $answer->getText();

            if ($password == 'secret') {
               $this->showOptions();
            } else {
                 $this->say('Contraseña equivocada.');
            }
        });
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->askPassword();
    }
}
