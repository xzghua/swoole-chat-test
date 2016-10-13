<?php
/**
 * Created by PhpStorm.
 * User: ylsc
 * Date: 16-10-13
 * Time: 上午11:51
 */

    $pdo = new PDO('mysql:host=localhost;charset=utf8;dbname=chat;port=3306','root','123456');
    $redis = new Redis();
    $result = $redis->connect('127.0.0.1', 6379);

    $ws = new swoole_websocket_server("0.0.0.0", 9502);

    $time = date('Y-m-d H:i:s',time());
    $sql = "insert into chat (content,user_name,created_at,user_face)values(?, ?,'{$time}',?)";
    $stmt = $pdo->prepare($sql);


    //监听WebSocket连接打开事件
    $ws->on('open', function (swoole_websocket_server $ws, $request) {
//        echo $request->fd."\r\n";
        $ws->push($request->fd, "hello, welcome\n");
    });

    //监听WebSocket消息事件
    $ws->on('message', function (swoole_websocket_server $ws, $frame) use ($stmt,$redis) {

        $messageData = json_decode($frame->data);

        $arr = array(
            $messageData->content,
            $messageData->userName,
            $messageData->userFace
        );

        $stmt->execute($arr);
        $group = [
            'id'   => $frame->fd,
            'name' => $messageData->userName,
            'face' => $messageData->userFace
        ];
        $redis->zAdd('chat_group_users',$frame->fd,json_encode($group));
//        $redis->sAdd('chat_group_user',$frame->fd);

        //尼玛的文档,在评论找到这条。。。。。
        foreach ($ws->connections as $fd) {
            $ws->push($fd,$frame->data);
        }

    });


    //监听WebSocket连接关闭事件
    $ws->on('close', function ($ws, $fd) use ($redis) {
        $redis->zRemRangeByScore('chat_group_users',$fd,$fd);//删除下线的
//        echo "client-{$fd} is closed\n";
    });

    $ws->start();