<?php
/**
 * workPcntl.php
 * 工作进程
 * 作者: 李红生 (549940183@qq.com)
 * 创建日期: 16/12/14 下午11:39
 * 修改记录:
 *
 * $Id$
 */
class workPcntl {
    public $mongodb = '😬';
    public $mongoModel;
    public $msgModel;
    public $mysqlModel;
    public function __construct() {
        $this->mongoModel =  new MongoClient($this->mongodb);
    }
    public function setMsgModel(MsgQueue $model) {
        $this->msgModel = $model;
    }
    //根据需求自行修改，子进程工作进程
    public function run() {
        //消耗队列数据，并执行自身业务
    }
}
/**
 * 启动 程序
 * 需引入 msgQueue.php,pcntl.php
 * Class StartWork
 */
include_once('pcntl.php');
include_once('msgQueue.php');
class StartWork {
    public $pcntlModel;
    public $msgModel;
    public $workModel;
    public function __construct() {
        $this->pcntlModel = new PcntlMaster();
        $this->msgModel   = new MsgQueue();
        $this->workModel  = new workPcntl();
        $this->workModel->setMsgModel($this->msgModel);
    }
    public function start() {
        //设置住进程需要运行的工作，根据业务自行修改
        $this->pcntlModel->masterWork = function() {
            //入队列数据，根据自身业务存数据
            $end = 10;
            $tmp = 0;
            while($tmp <= $end) {
                $status = $this->msgModel->send($tmp);
                $tmp++;
            }
            //主进程监控队列，无数据消灭队列
            while(true) {
                $msgStatus = $this->msgModel->getStatus();
                if($msgStatus['msg_qnum'] < 1) {
                    $this->msgModel->close();
                    break;
                }
            }
            //正确消灭进程
            exit(0);
        };
        //启动进程，并创建两个子进程
        $this->pcntlModel->set($this->workModel,'run',2);
        $this->pcntlModel->start();
    }
}

try {
    $startWork = new StartWork();
    $startWork->start();
} catch (Exception $e) {
    exit(0);
}