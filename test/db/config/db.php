<?php
return [
    'dsn' => 'mysql:host=centos;dbname=test',
    'username' => 'hftest',
    'password' => '123456',
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false
    ],
    'backup' => [
        'dsn' => 'mysql:host=centos;dbname=test',
        'username' => 'hftest',
        'password' => '123456',
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true
        ],
    ],
    'custom' => [
        'dsn' => 'mysql:host=centos;dbname=test',
        'username' => 'hftest',
        'password' => '123456',
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true
        ],
    ],
    'invalid' => [
     ]
];
