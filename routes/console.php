<?php

Schedule::command('s3:cleanup-documents')
    ->dailyAt('03:00')
    ->withoutOverlapping();
