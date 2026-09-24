<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('clinic:send-reminders')->dailyAt('07:00');
