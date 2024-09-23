<?php namespace App;

use App\Mail;

class cron extends Controller
{
    function index()
    {
        Mail::send("vzakorz@gmail.com", "Cron job", "Cron job is working");
        stop(200);

    }

}
