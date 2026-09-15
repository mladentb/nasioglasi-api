<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('listings:expire')->daily();
