<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('botman:configure-url', function () {
    $botmanUrl = config('app.url').'/botman';

    $telegramUrl = sprintf('https://api.telegram.org/bot%s/setWebhook', config('services.botman.telegram_token'));

    $client = new GuzzleHttp\Client;

    $response = $client->request('POST', $telegramUrl, ['json' => ['url' => $botmanUrl]]);

    echo $response->getStatusCode(); // 200
    echo $response->getReasonPhrase(); // OK
})->describe('Display an inspiring quote');

// curl -F “url=https://<YOURDOMAIN.EXAMPLE>/<WEBHOOKLOCATION>"
