<?php
return [
    'name' => 'test',
    'version' => '1.0.0',
    'options' => [
        [
            'name' => 'a',
        ],
        [
            'name' => 'b',
        ]
    ],
    'mutually_exclusive_option_groups' => [
        ['a', 'b', 'required' => true]
    ],
    'class' => 'Hyperframework\Cli\Test\NoArgumentCommand'
];
