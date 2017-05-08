<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=phatt-cluster-1.cluster-cch7ui9gbr3f.us-east-1.rds.amazonaws.com;dbname=phatt',
            'username' => 'root',
            'password' => 'th3m3d14l4b',
            'charset' => 'utf8',
        ],    
    	/*
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        */
    ],
];
