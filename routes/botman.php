<?php

use App\Http\Controllers\BotManController;
use Mpociot\BotMan\Middleware\ApiAi;

$botman = resolve('botman');

//// Don't use the Facade in here to support the RTM API too :)
//
//$botman->hears('help', BotManController::class.'@startConversation');
//$botman->hears('ajuda', BotManController::class.'@startConversation');
//
//$botman->hears('hello', function($bot) {
//    $bot->reply('hello!');
//});
//
//$botman->hears('hi', function($bot) {
//    $bot->reply('hi!');
//});
//
//$botman->hears('test', function($bot) {
//    $bot->reply('what test?');
//});

$botman->hears('oi', function($bot) {
    $firstName = strtolower($bot->getUser()->getFirstName());

    if (strpos($firstName, 'anselmo') !== false) {
        $message = 'Fala, Hagrid!';
    } elseif (strpos($firstName, 'leandro') !== false) {
        $message = 'Fala, Lelê!';
    } else {
        $message = 'olá!';
    }

    $bot->reply($message);
});

$botman
    ->hears('.*', BotManController::class.'@talk')
    ->middleware(ApiAi::create('c4e3f6f7e2e749dbb4adf6bc4d4e93be')->listenForAction());
