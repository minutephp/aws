<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_time_limit(0);
    ignore_user_abort(true);

    $log = __DIR__ . '/log';
    @mkdir($log, 0777, true);

    $path = $_SERVER['REQUEST_URI'] ?: $_SERVER['PATH_INFO'];
    $file = sprintf('php /var/www/vendor/minutephp/cron/cli/bin/%s --input="%s" --queue-name="%s" --queue-msg-id="%s" --queue-path="%s"', $path == '/scheduled' ? 'cron-runner' :
        'script-runner', file_get_contents('php://input'), $_SERVER['HTTP_X_AWS_SQSD_QUEUE'], $_SERVER['HTTP_X_AWS_SQSD_MSGID'], $_SERVER['HTTP_X_AWS_SQSD_PATH']);
    $cmd  = sprintf('%s > %s/run.log 2>&1 &', $file, $log);

    file_put_contents("$log/daemon.log", sprintf("%s - %s\n", @date('Y-m-d H:i:s'), $cmd), FILE_APPEND);
    file_put_contents("$log/cron.log", var_export(['server' => $_SERVER, 'request' => $_REQUEST], true));

    chdir('/var/www');
    pclose(popen($cmd, 'r'));
}