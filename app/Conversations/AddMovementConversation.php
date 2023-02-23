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
            $this->ask('Ingrese la cantidad de el gasto', function(Answer $ans) {
                $this->amount = $ans->getText();

                $movement = new Movement();
                $movement->description = $this->description;
                $movement->type = 'gasto';
                $movement->amount = $this->amount;
                $movement->attatch = '';
                $movement->save();
                $this->say('Gasto guardado exitosamente');
            });
        });
    }

    public function addIncome()
    {
        $this->ask('Ingrese el nombre del ingreso', function(Answer $answer) {
            $this->description = $answer->getText();
            $this->ask('Ingrese la cantidad del ingreso', function(Answer $answer) {
                $this->amount = $answer->getText();

                $movement = new Movement();
                $movement->description = $this->description;
                $movement->type = 'ingreso';
                $movement->amount = $this->amount;
                $movement->attatch = '';
                $movement->save();
                $this->say('Ingreso guardado exitosamente');
            });
        });
    }

    public function showMovements()
    {
        $movements = Movement::latest()->limit(10)->get();
        foreach ($movements as $movement) {
          $this->say('Se '.$movement->type.' $'.$movement->amount.' el ' .$movement->created_at. ' por el concepto de '.$movement->description);
        }
    }

    public function showOptions()
    {
        $question = Question::create('Hola vecino administrador, ¿En que le puedo ayudar?')
            ->fallback('No puedo ayudarle')
            ->callbackId('canthelp')
            ->addButtons([
                Button::create('Agregar gasto')->value('gasto'),
                Button::create('Agregar ingreso')->value('ingreso'),
                Button::create('Mostrar movimientos')->value('movements'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'gasto' or 'ingreso'
                if ($selectedValue == 'gasto') {
                    $this->addExpense();
                } else if ($selectedValue == 'ingreso') {
                    $this->addIncome();
                } else if ($selectedValue == 'movements') {
                    $this->showMovements();
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
