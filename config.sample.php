<?php

return [
    'main' => [
        'fhx' => 1, //是否开启反和谐，1为开启，0为不开启
        'cache_res' => 1, //是否缓存资源文件
        'values' => [3, 2, 0.1, 6], //资源价值，油弹钢铝，越大价值越高，必须大于0
        'points' => [0, 0, 0, 0], //强化属性价值，火雷甲空，越大价值越高，计算时会额外附加到拆解价值上，不能小于0
        'war_log' => 0, //是否记录战斗数据，1为开启，0为不开启
        'war_log_path' => '{uid}/{type}/{year}{month}{day}_{hour}{min}{sec}_{map}', //战斗记录保存路径，不要包含中文，可用变量：{uid} {type} {year} {month} {day} {hour} {min} {sec} {map}
        'war_replay' => 0, //是否开启战斗回放功能，1为开启，0为不开启
    ],
    'web_server' => [//Web服务器设置
        'port' => 14200,
    ],
    'proxy_server' => [//代理服务器设置
        'port' => 14201,
    ],
    'debug' => [
        'save_api_transmission' => 0, //是否记录api请求及返回结果：0-不记录，1-记录新增的，2-记录所有
    ],
];
