<?php
/**
 * pctnl.php
 * 进程控制类
 * 作者: 李红生 (549940183@qq.com)
 * 创建日期: 16/12/14 下午11:34
 * 修改记录:
 *
 * $Id$
 */

class PcntlMaster
{
    //最多开启的进程数
    private $size;
    //
    private $currSize=0;//当前进程数
    //运行的程序对象
    private $pro;
    //运行的方法
    private $run;
    //主线程工作
    public $masterWork;
    public function __construct() {
        //进程守护
        $this->daemonize();
    }
    /*
    *构造函数
    *@param string 对象
    *@param string 方法名
    *@param int    要开启的子进程数量
    *
    */
    public function set($controller,$active,$size) {
        $this->size = $size;
        $this->pro  = $controller;
        $this->run  = $active;
    }
    /*cli模式下运行
    *进程守护化函数，使进程脱离当前终端控制，以便后台独立运行。
    执行后需要通过 ps - kill 杀死此进程，
    或者 运行 posix_getpid() 获取 当前进程ID 然后kill
    如果不是service 服务，只是运行时间比较长，最好是在业务程序里加上 exit退出进程。
    */
    function daemonize() {
        $pid = pcntl_fork();//创建子进程
        if($pid == -1) {
            exit('创建进程失败');
        } else if($pid > 0) {
            //让父进程退出。以便开启新的会话
            exit(0);
        }
        //建立一个有别于终端的新的会话,脱离当前会话终端，防止退出终端的时候，进程被kill
        posix_setsid();
        $pid = pcntl_fork();
        if($pid == -1) {
            die('创建进程失败');
        } else if($pid > 0) {
            //父进程推出剩下的子进程为独立进程，归为系统管理此进程
            exit(0);
        }
    }
    //运行子进程程序
    public function start() {
        $i = 0;
        while ($i<$this->size) {
            $i++;
            $pid = pcntl_fork();
            if($pid == 0) {

                //创建子进程成功，并运行子进程需要运行的程序
                $run = $this->run;
                $this->pro->$run();
            }else if ($pid == -1) {
                //失败重新创建子进程
                if($i>0) {
                    $i--;
                }
            }
        }
        //运行master进程需要程序
        call_user_func($this->masterWork, $this);
        //监控子进程退出
        $p = pcntl_wait($status);
    }
}
