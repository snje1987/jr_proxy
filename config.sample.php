<?php

return [
    'main' => [
        'tmp_dir' => '/tmp', //临时文件保存目录
        'fhx' => 1, //是否开启反和谐，1为开启，0为不开启
    ],
    'web_server' => [//Web服务器设置
        'port' => 14200,
    ],
    'proxy_server' => [//代理服务器设置
        'port' => 14201,
    ],
    'debug' => [
        'save_api_transmission' => 0, //是否记录api请求及返回结果
    ],
];
