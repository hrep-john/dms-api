<?php

return [
    'log_group' => strtolower(str_replace(' ', '-', env('APP_NAME', 'laravel_app'))),
    'stream_name' => strtolower(str_replace(' ', '-', env('APP_NAME', 'laravel_app')))
];
