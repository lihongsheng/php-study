<?php
/**
 * msgQueue.php
 * 队列类，基于Linux
 * 作者: 李红生 (549940183@qq.com)
 * 创建日期: 16/12/14 下午11:37
 * 修改记录:
 *
 * $Id$
 */

class MsgQueue
{
    //队列id
    private  $msgId = '66000';
    //队列标示
    private  $msgKey;
    public function __construct($msgId)
    {
        $this->msgId = $msgId;
        if(!function_exists('msg_send')) {
            throw new Exception("MSG_SEND NOT FIND");
        }
        if(!msg_queue_exists($this->msgId)){
            //创建队列
            $this->msgKey = msg_get_queue($this->msgId);
        } else {
            $this->msgKey = msg_get_queue($this->msgId);
            msg_remove_queue($this->msgKey);
            $this->msgKey = msg_get_queue($this->msgId);
            //throw new Exception("QUEUE IS SET");
        }
    }
    /**
     * 发送队列
     * @param $msg
     */
    public function send($msg) {
        msg_send($this->msgKey, 1, $msg,true);
    }
    /**
     * 取队列值
     * @return bool
     */
    public function get() {
        $data = msg_receive($this->msgKey, 0, $msgType, 1024, $message);
        if($data) {
            return $message;
        }
        return false;
    }
    /**
     * 获取队列状态
     * @return array
     */
    public function getStatus() {
        return msg_stat_queue($this->msgKey);
    }
    /**
     * 移除队列
     * @return bool
     */
    public function close() {
        return msg_remove_queue($this->msgKey);
    }
    /**
     * 判断队列是否存在
     * @return bool
     */
    public function isDelete() {
        return msg_queue_exists($this->msgId);
    }
}
