<?php

$date = date("Y-m-d");
$projectAppName = strtolower(str_replace(' ', '-', env('APP_NAME', 'laravel_app')));

return [
    'log_group' => '/aws/elastic-beanstalk/' . $projectAppName,
    'stream_name' => $projectAppName . '/' . $date
];
