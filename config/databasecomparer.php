<?php
// config for DieterCoopman/DatabaseComparer
return [
    'connections' => [
        'source' => [
            "dbname"   => "",
            "user"     => "",
            "password" => "",
            "host"     => "",
            "port"     => "",
            "driver"   => "",
            "ssh"      => ""
        ],
        'target' => [
            "dbname"   => "",
            "user"     => "",
            "password" => "",
            "host"     => "",
            "port"     => "",
            "driver"   => "",
            "ssh"      => ""
        ]
    ],
    'sqlfile'     => 'database/comparison.sql'
];
