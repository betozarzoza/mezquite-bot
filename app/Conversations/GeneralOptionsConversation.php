<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Http;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Carbon\Carbon;
use App\Movement;
use App\General;
use App\Number;
use App\Schedule;
use App\Balance;

class GeneralOptionsConversation extends Conversation
{
    protected $description;
    protected $house;
    protected $date;
    protected $month;
    protected $hour;
    protected $duration;
    protected $house_number;
    protected $day;

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
        $this->ask('Ingrese el nombre de el evento', function(Answer $answer) {
            $this->description = $answer->getText();
            $this->ask('¿De que casa es? Ingrese solo el numero', function(Answer $answer) {
                $this->house = $answer->getText();
                $question = Question::create('¿Que mes quiere hacer el evento?')
                ->fallback('No puedo ayudarle')
                ->callbackId('canthelp')
                ->addButtons([
                    Button::create('Enero')->value('01'),
                    Button::create('Febrero')->value('02'),
                    Button::create('Marzo')->value('03'),
                    Button::create('Abril')->value('04'),
                    Button::create('Mayo')->value('05'),
                    Button::create('Junio')->value('06'),
                    Button::create('Julio')->value('07'),
                    Button::create('Agosto')->value('08'),
                    Button::create('Septiembre')->value('09'),
                    Button::create('Octubre')->value('10'),
                    Button::create('Noviembre')->value('11'),
                    Button::create('Diciembre')->value('12'),
                ]);
                $this->ask($question, function(Answer $answer) {
                    $this->month = $answer->getText();
                    $this->ask('Ingrese el dia de el evento en numero', function(Answer $answer) {
                        $this->day = strlen($answer->getText()) > 1 ? $answer->getText() : '0'.$answer->getText();
                        $this->say('2023-'.$this->month.'-'.$this->day);
                        $this->ask('Ingrese la hora de el evento en este formato HH:MM (24hrs), ejemplo: 17:00 para las cinco de la tarde.', function(Answer $answer) {
                            $this->hour = $answer->getText();
                            $this->ask('Ingrese la duracion de el evento en horas (Solo un numero, ejemplo: 2 para dos horas.)', function(Answer $answer) {
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
        });
    }

    public function howMuchDoIOwe()
    {
        $this->ask('Hola vecino, ¿De que casa es?, por favor escriba solo el numero de la casa', function(Answer $answer) {
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

    public function showGeneralBalance()
    {
        $balance = General::where('name', 'balance')->first();
        $this->say('El saldo de el condominio es '.$balance->value);
    }


    public function showAgenda()
    {
        $this->ask('Hola vecino, ¿De que casa quisiera saber el telefono?, por favor escriba solo el numero de la casa', function(Answer $answer) {
            $house_number = $answer->getText();
            $numbers = Number::where('house', intval($house_number))->get();

            if (count($numbers)) {
                foreach ($numbers as $number) {
                  $this->say('Casa '.$house_number.' - '.$number->name);
                  $this->say($number->number);
                }
            } else {
                 $this->say('No se encontro ningun telefono asociado a esa casa.');
            }
        });
    }

    public function openGate () {
        Http::get(' https://www.virtualsmarthome.xyz/url_routine_trigger/activate.php?trigger=d8836335-b02b-436b-90d2-f8b2d2f2ed22&token=24c60b0f-ff93-43b0-8cd7-83af818096c9&response=json');
        $this->say('Abriendo porton...');
    }

    public function showOptions()
    {
        $question = Question::create('Hola vecino, ¿En que le puedo ayudar?')
            ->fallback('No puedo ayudarle')
            ->callbackId('canthelp')
            ->addButtons([
                Button::create('Abrir porton')->value('open_gate'),
                //Button::create('Mostrar movimientos')->value('movements'),
                //Button::create('Saldo del Condominio')->value('balance'),
                //Button::create('Eventos programados')->value('show_events'),
                //Button::create('Agendar evento')->value('schedule_event'),
                //Button::create('Cuanto debo')->value('how_much_i_owe'),
                //Button::create('Contactos de vecinos')->value('agenda'),
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
                } else if ($selectedValue == 'how_much_i_owe') {
                    $this->howMuchDoIOwe();
                } else if ($selectedValue == 'agenda') {
                    $this->showAgenda();
                } else if ($selectedValue == 'open_gate') {
                    $this->openGate();
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
