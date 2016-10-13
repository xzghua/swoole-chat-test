<?php
/**
 * Created by PhpStorm.
 * User: ylsc
 * Date: 16-10-13
 * Time: 下午4:43
 */

    $redis = new Redis();
    $result = $redis->connect('127.0.0.1', 6379);

    $redisCount = $redis->zRange('chat_group_users', 0, -1);//返回所有个数

    $newData = [];

    foreach ($redisCount as $value) {
        $newData[] = json_decode($value , true);
    }
    $returnData['status'] = 1;
    $returnData['msg'] = 'ok';
    $returnData['data'] = $newData;

    echo json_encode($returnData);