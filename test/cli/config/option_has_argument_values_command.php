<?php
return [
    'name' => 'test',
    'version' => '1.0.0',
    'options' => [
        [
            'name' => 'test',
            'argument' => [
                'name' => 'arg',
                'values' => ['a', 'b']
            ]
        ]
    ],
    'class' => 'Hyperframework\Cli\Test\NoArgumentCommand'
];
