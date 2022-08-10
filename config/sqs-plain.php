<?php

/**
 * List of plain SQS queues and their corresponding handling classes
 */
return [
    'handlers' => [
        'base-integrations-updates' => App\Jobs\SQSHandlerJob::class,
        env('TEXTRACT_SNS_TOPIC') => App\Jobs\TextractTopicToSqs::class,
    ],

    'default-handler' => App\Jobs\SQSHandlerJob::class
];