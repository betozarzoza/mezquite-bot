<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Carbon\Carbon;
use App\Movement;
use App\General;
use App\Schedule;

class GeneralOptionsConversation extends Conversation
{
    protected $description;
    protected $house;
    protected $date;
    protected $hour;
    protected $duration;

    public function showMovements()
    {
        $movements = Movement::latest()->limit(10)->get();
        foreach ($movements as $movement) {
          $this->say('Se '.$movement->type.' $'.$movement->amount.' el ' .$movement->created_at. ' por el concepto de '.$movement->description);
        }
    }

    public function nextSchedules()
    {
        $schedules = Schedule::whereDate('date', '>', '2023-02-23')->orderBy('date', 'asc')->get();
        if (count($schedules)) {
            $this->say('Estos son los proximos eventos programados:');
            foreach ($schedules as $schedule) {
              $this->say($schedule->description.' el dia '.Carbon::createFromFormat('Y-m-d H:i:s',$schedule->date)->toDayDateTimeString().' por la casa ' .$schedule->house. ' con una duracion de '.$schedule->duration.' horas');
            }
        } else {
            $this->say('No hay eventos programados proximos');
        }
    }

    public function scheduleEvent()
    {
        $this->ask('Ingrese la descripcion de el evento', function(Answer $answer) {
            $this->description = $answer->getText();
            $this->ask('¿De que casa es? Ingrese solo el numero', function(Answer $answer) {
                $this->house = $answer->getText();
                $this->ask('Ingrese la fecha de el evento en este formato: AAAA-MM-DD', function(Answer $answer) {
                    $this->date = $answer->getText();
                    $this->ask('Ingrese la hora de el evento en este formato HH:MM', function(Answer $answer) {
                        $this->hour = $answer->getText();
                        $this->ask('Ingrese la duracion de el evento en horas', function(Answer $answer) {
                            $this->duration = $answer->getText();
                                $dt = Carbon::createFromFormat('Y-m-d H:i:s',  $this->date . ' ' . $this->hour.':00');
                                $this->ask('Confirma que desea agendar un evento para el dia '. $dt->toDayDateTimeString().'? Si/No', function(Answer $answer) {
                                    if ($answer->getText() == 'Si' || $answer->getText() == 'si') {
                                        $from_date = (Carbon::createFromFormat('Y-m-d H:i:s',  $this->date . ' ' . $this->hour.':00'));
                                        $to_date =   (Carbon::createFromFormat('Y-m-d H:i:s',  $this->date . ' ' . $this->hour.':00')->addHours($this->duration));
                                        $count =  Schedule::whereBetween('date', [$from_date, $to_date])->count();
                                        if ($count > 0) {
                                            $this->say('No se puede agendar ese dia porque hay un evento agendado, te mostrare los proximos eventos.');
                                            $this->nextSchedules();
                                        } else {
                                            $schedule = new Schedule();
                                            $schedule->description = $this->description;
                                            $schedule->house = $this->house;
                                            $schedule->date = $this->date . ' ' . $this->hour.':00';
                                            $schedule->duration = $this->duration;
                                            $schedule->save();
                                            $this->say('Evento agendado exitosamente, disfrute su evento vecino!');
                                        }
                                    } else if ($answer->getText() == 'No' || $answer->getText() == 'no') {
                                        $this->say('Evento por agendar cancelado');
                                        $this->showOptions();
                                    }
                                });
                        });
                    });
                });
            });
        });
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
                Button::create('Eventos programados')->value('show_events'),
                Button::create('Agendar evento')->value('schedule_event'),
            ]);

        $this->ask($question, function (Answer $answer) {
            // Detect if button was clicked:
            if ($answer->isInteractiveMessageReply()) {
                $selectedValue = $answer->getValue(); // will be either 'gasto' or 'ingreso'
                if ($selectedValue == 'movements') {
                    $this->showMovements();
                } else if ($selectedValue == 'balance') {
                    $this->showGeneralBalance();
                } else if ($selectedValue == 'show_events') {
                    $this->nextSchedules();
                } else if ($selectedValue == 'schedule_event') {
                    $this->scheduleEvent();
                } else {
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
