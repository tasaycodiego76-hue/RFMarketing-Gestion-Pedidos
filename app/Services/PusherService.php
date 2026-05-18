<?php

namespace App\Services;

use Pusher\Pusher;

class PusherService
{
    private Pusher $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_KEY'),
            env('PUSHER_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_CLUSTER'), 'useTLS' => true]
        );
    }

    public function emitir(string $canal, string $evento, array $data): void
    {
        $this->pusher->trigger($canal, $evento, $data);
    }
}