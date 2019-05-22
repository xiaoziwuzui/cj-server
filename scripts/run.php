<?php
/**

 * User: xiaojiang432524@163.com
 * Date: 2017/7/3-15:35
 * 用户单日收入结算脚本
 */
define('APP_ROOT', dirname(dirname(__FILE__)) . '/');
define('FLIB_RUN_MODE', 'manual');
define('PUBLIC_ROOT', APP_ROOT . 'public/');
define('UPLOAD_ROOT', APP_ROOT . 'public/uploads/');

require_once APP_ROOT . "flib/Flib.php";

set_time_limit(0);

global $_F;

if($_SERVER['USER'] == 'jiangtaiping'){
    $_F['dev_mode'] = true;
}


//$incomeTable = new FTable('channel_income');
//$settlementTable = new FTable('channel_settlement');
//$incomeData = array();
//$s_data = $settlementTable->fields('uid,date,money,bonus,group_money')->where(array('date'=>$report_date))->select();
//
//foreach ($s_data as $k=>$v){
//    $i_data = $incomeTable->fields('sum(money) as money,sum(bonus) as bonus')->where(array('date'=>$v['date'],'uid'=>$v['uid']))->find();
//    if(isset($incomeData[$v['date']][$v['uid']])){
//        echo $v['uid'],chr(10);
//    }else{
//        $incomeData[$v['date']][$v['uid']] = array(
//            'all_money' => $v['money'],
//            'all_bonus' => $v['bonus'],
//            'all_group_money' => $v['group_money'],
//            'sum_money' =>$i_data['money'],
//            'sum_bonus' =>$i_data['bonus'],
//        );
//    }
//}
//foreach ($incomeData as $date=>$list){
//    foreach ($list as $uid=>$v){
//        $all = $v['all_money'] + $v['all_bonus'] + $v['all_group_money'];
//        $sum = $v['sum_bonus'] + $v['sum_money'];
//        if($all != $sum){
//            echo $date,':',$uid,':',$all,'=>',$sum,chr(10);
//        }
//    }
//}
$page = intval($argv[1]);
if($page <= 0){
    $page = 1;
}
//$table = new FTable('article');
//$art = $table->fields('id,content')->where(array('word'=>0,'picture'=>0))->page($page)->limit(500)->select();
//
//foreach ($art as $v){
//    if($v['content'] != ''){
//        $word    = mb_strlen(strip_tags($v['content']));
//        $picture = Service_Public::countImgTag($v['content']);
//        $result  = $table->where(array('id'=>$v['id']))->update1(array('word' => $word,'picture'=>$picture));
//        echo $v['id'],' ',$result,chr(10);
//    }
//}
//$table = new FTable('channel_income','i');
//$sysTable = new FTable('sys_income');
//$art = $table->fields('i.money,i.bonus,i.uid,i.create_time,t.mediaid,i.article_id')->leftJoin('task','t','i.taskid=t.id')->where(array('i.type'=>array('in'=>array(3,4))))->page($page)->limit(500)->select();
//
//foreach ($art as $v){
//    if($v['money'] + $v['bonus'] > 0){
//        $result = $sysTable->insert(array(
//            'media_id'    => $v['mediaid'],
//            'uid'         => $v['uid'],
//            'type'        => 2,
//            'money'       => $v['money'] + $v['bonus'],
//            'article_id'  => $v['article_id'],
//            'create_time' => $v['create_time'],
//            'editor'      => 0,
//        ));
//        echo $v['id'],' ',$result,chr(10);
//    }
//}
//$table = new FTable('manager_cash');
//$utable = new FTable('manager');
//$list = $table->fields('money,uid,id')->where(array('editor' => 0,'type'=>4))->select();
//foreach ($list as $k=>$v){
//    if($v['money'] >= 10000){
//        $m = $v['money'] / 100;
//        $f = $v['money'] - $m;
//        echo $v['money'],':',$m,':',$f,':','uid:',$v['uid'],chr(10);
////        $table->where(array('id'=>$v['id']))->update1(array('money'=>$m));
////        $utable->where(array('uid'=>$v['uid']))->increase('money',-$f);
//    }
//}
/**
 * 更新用户消费金额
 */
//$table = new FTable('manager_cash');
//$utable = new FTable('manager');
//$list = $utable->fields('uid')->select();
//$table = new FTable('manager_cash');
//foreach ($list as $k=>$v){
//    $incomeTable = new FTable('channel_income','i');
//    $count2 = $incomeTable->fields('sum(i.money) as money,sum(i.bonus) as bonus')->leftJoin('task','t','i.taskid=t.id')->where(array('i.type'=>array('in'=>array(1,2)),'t.mediaid'=>$v['uid']))->find();
//
//    $count = $table->fields('sum(money) as money')->where(array('uid'=>$v['uid'],'type'=>array('in'=>array(5,6))))->find();
//    $list[$k]['cc2_money'] = $count2['money'] + $count2['bonus'];
//    $list[$k]['cc_money'] = $count['money'];
//    $utable->where(array('uid'=>$v['uid']))->update1(array('consume_money'=>($count2['money'] + $count2['bonus'] + $count['money'])));
//}

//$frozenTable = new FTable('frozen_log','f');
//$fro = $frozenTable->fields('f.id,f.uid,f.money,f.task_id,t.status,f.status as fro_status')->leftJoin('task','t','f.task_id=t.id')->where(array('f.status'=>1,'t.status'=>3))->select();
//$uid = array();
//foreach ($fro as $fv){
//    $uid[$fv['uid']] = $fv['uid'];
//    Service_Manager::cashLog($fv['uid'],Service_Public::formatMoney($fv['money']),'任务:['.$fv['task_id'].']完成,退还冻结佣金',4,0);
//    $frozenTable->where(array('id'=>$fv['id']))->update1(array('status'=>2,'success_time'=>time()));
//    echo $fv['uid'],chr(10);
//}
//
//foreach ($uid as $v){
//    Services_Cache::getMediaTotal($v,true);
//}
//
//echo 'success',chr(10);
/**
 * 整理数据功能
 */
//转移消费表类型
function a($page = 1){
    $incomeTable = new FTable('manager_cash');
    $list = $incomeTable->fields('id,type,remark')->where(array('type'=>1))->page($page)->limit(500)->order(array('id'=>'desc'))->select();
    foreach ($list as $v){
        if(mb_substr($v['remark'],0,4) == '审核文章'){
            $incomeTable->where(array('id'=>$v['id']))->update1(array('type' => 11));
            echo $v['id'],chr(10);
        }
    }
    if(count($list) >= 500){
        a($page +1);
    }
}
/**
 * 导入冻结金额
 * 佣金增加
 */
function b($page = 1){
    $taskTable = new FTable('task');
    $frozenTable = new FTable('frozen_log');
    $incomeTable = new FTable('manager_cash');
    $list = $incomeTable->fields('id,type,remark,money,uid')->where(array('type'=>1))->page($page)->limit(500)->order(array('id'=>'desc'))->select();
    foreach ($list as $v){
        if(strpos($v['remark'],'佣金增加')){
            $task_id = str_replace('任务:[','',$v['remark']);
            $task_id = str_replace(']佣金增加,预扣佣金','',$task_id);
            if(!Service_Public::valiTaskID($task_id)){
                $task = $taskTable->fields('money,total')->where(array('id'=>$task_id))->find();
                if($task){
                    $result = $frozenTable->fields('id,money,status')->where(array('task_id'=>$task_id))->find();
                    if($result && $result['money'] != $task['money'] * $task['total']){
                        $frozenTable->where(array('id'=>$result['id']))->increase('money',$v['money']);
                        if($result['status'] == 2){
                            //多退一次余额
                            Service_Manager::cashLog($v['uid'],Service_Public::formatMoney($v['money']),'任务:['.$task_id.']退还冻结金额',4,0);
                        }
                        echo $task_id,chr(10);
                    }
                }
            }
        }
    }
    if(count($list) >= 500){
        b($page +1);
    }
}
/**
 * 减少的佣金
 * @param int $page
 * @author 93307399@qq.com
 */
function c($page = 1){
    $taskTable = new FTable('task');
    $frozenTable = new FTable('frozen_log');
    $incomeTable = new FTable('manager_cash');
    $list = $incomeTable->fields('id,type,remark,money,uid')->where(array('type'=>4))->page($page)->limit(500)->order(array('id'=>'desc'))->select();
    foreach ($list as $v){
        if(strpos($v['remark'],'佣金减少')){
            $task_id = str_replace('任务:[','',$v['remark']);
            $task_id = str_replace(']佣金减少,退还佣金','',$task_id);
            if(!Service_Public::valiTaskID($task_id)){
                $task = $taskTable->fields('money,total')->where(array('id'=>$task_id))->find();
                if($task){
                    $result = $frozenTable->fields('id,money,status')->where(array('task_id'=>$task_id))->find();
                    if($result && $result['money'] != $task['money'] * $task['total']){
                        $frozenTable->where(array('id'=>$result['id']))->increase('money',-$v['money']);
                        if($result['status'] == 2){
                            //扣除多退的余额
                            Service_Manager::cashLog($v['uid'],Service_Public::formatMoney($v['money']),'任务:['.$task_id.']多退冻结金额扣除',1,0);
                        }
                        echo $task_id,chr(10);
                    }
                }
            }
        }
    }
    if(count($list) >= 500){
        c($page +1);
    }
}

/**
 * 将之前的所有任务时间更改为23点过期
 * @param int $page
 * @author 93307399@qq.com
 */
function tt($page = 1){
    $taskTable = new FTable('task');
    $list = $taskTable->fields('id,to_time')->where(array('to_time'=>array('gt'=>0)))->page($page)->limit(500)->order(array('id'=>'desc'))->select();
    foreach ($list as $v){
        $taskTable->where(array('id'=>$v['id']))->update1(array('to_time' => strtotime(date('Y-m-d',$v['to_time']).' 23:59:59')));
    }
    if(count($list) >= 500){
        tt($page +1);
    }
}
//a(1);
//b(1);
//c(1);
tt(1);
echo 'success',chr(10);