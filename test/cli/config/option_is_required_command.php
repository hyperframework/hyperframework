<?php
return [
    'name' => 'test',
    'version' => '1.0.0',
    'options' => [
        [
            'name' => 'test',
            'short_name' => 't',
            'required' => true
        ], [
            'name' => 'version'
        ]
    ],
    'class' => 'Hyperframework\Cli\Test\NoArgumentCommand'
];
