<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});

$botman->hears('Chip', function ($bot) {
    $bot->reply('& Beily!');
});

$botman->hears('Start conversation', BotManController::class.'@startConversation');
