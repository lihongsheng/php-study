<?php

//实际工作者
class WebWork extends Stackable {

    public function __construct() {
        $this->complete = false;
    }

    public function run() {
        $this->worker->logger->log(
            "%s executing in Thread #%lu",
            __CLASS__, $this->worker->getThreadId());
        usleep(100);
        $this->complete = true;
    }

    public function isComplete() {
        return $this->complete;
    }

    protected $complete;
}

//工作控制者
class WebWorker extends Worker {

    public function __construct(WebLogger $logger) {
        $this->logger = $logger;
    }

    protected $logger;
}

//业务逻辑
class WebLogger extends Stackable {

    protected function log($message, $args = []) {
        $args = func_get_args();

        if (($message = array_shift($args))) {
            echo vsprintf(
                "{$message}\n", $args);
        }
    }
}

$logger = new WebLogger();
//初始化线程
$pool = new Pool(8, WebWorker::class, [$logger]);

while (@$i++<10)
    //提交实际工作者
    $pool->submit(new WebWork());

usleep(2000000);

$logger->log("Shrink !!");

$pool->resize(1);
$pool->collect(function(WebWork $task){
    return $task->isComplete();
});

while (@$j++<10)
    $pool->submit(new WebWork());

$pool->shutdown(); 