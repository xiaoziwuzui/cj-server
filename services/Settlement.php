<?php

/**
 * Created by phpstorm
 * @name Services_Settlement 结算类相关方法
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */
class Services_Settlement
{

    public static $table = 'r';

    public static function debug($var,$ext = false){
        global $_F;
        if($_F['debug']){
            print_r($var);
            echo chr(10);
            if($ext !== false){
                exit(0);
            }
        }
    }

    /**
     * 通道日详细数据报表
     * @param string $date
     * @author 93307399@qq.com
     * @return bool
     */
    public static function reportChannelDayDetail($date = ''){
        global $_F;
        $dataTable = new FTable('report_day_channel_detail','',self::$table);
        $Data      = array();
        $emptyItem = array(
            'date'          => $date,
            'cid'           => 1,
            'total_order'   => 0,
            'success_order' => 0,
            'total_money'   => 0,
            'am_total'      => 0,
            'am_money'      => 0,
            'am_data'       => array(),
            'pm_total'      => 0,
            'pm_money'      => 0,
            'pm_data'       => array(),
        );
        /**
         * 获取总订单数量
         */
        $totalData = self::_ChannelOrderDayTotal($date);
        /**
         * 获取付款订单数和总金额
         */
        $payData = self::_ChannelOrderDayPay($date);
        /**
         * 获取当日上午下午的数据
         */
        $hourData = array();
        $hourData = self::_ChannelDayAmOrPm($date);
        /**
         * 将数据汇总
         */
        foreach ($totalData as $k=>$v){
            $item                = $emptyItem;
            $item['cid']         = $v['cid'];
            $item['total_order'] = $v['total_order'];
            $Data[$k]            = $item;
        }
        self::debug($payData);
        foreach ($payData as $k=>$v){
            $item = isset($Data[$k]) ? $Data[$k] : $emptyItem;
            $item['cid']           = $v['cid'];
            $item['success_order'] = $v['total_order'];
            $item['total_money']   = $v['total_money'];
            $Data[$k]              = $item;
        }
        self::debug($Data);
        self::debug($hourData);
        //将时段数据分为白天(6:00-12:00)和晚上(00:00-6:00).
        foreach ($hourData as $k=>$v){
            $item = isset($Data[$v['cid']]) ? $Data[$v['cid']] : $emptyItem;
            $item['cid'] = $v['cid'];
            if($v['pay_hour'] < 6){
                $item['am_total'] += $v['total_order'];
                $item['am_money'] += $v['total_money'];
                $item['am_data'][$v['pay_hour']] = array(
                    'o' => intval($v['total_order']),
                    'm' => intval($v['total_money']),
                );
            }else  if($v['pay_hour'] >= 6){
                $item['pm_total'] += $v['total_order'];
                $item['pm_money'] += $v['total_money'];
                $item['pm_data'][$v['pay_hour']] = array(
                    'o' => intval($v['total_order']),
                    'm' => intval($v['total_money']),
                );
            }
            $Data[$v['cid']] = $item;
        }
        unset($hourData);
        self::debug($Data);
        /**
         * 入库到日详细报表
         */
        foreach ($Data as $key=>$data){
            $data['update_time'] = time();
            $result = array();
            try{
                $result = $dataTable->fields('id')->where(array('cid'=>$data['cid'],'date'=>$date))->find();
            }catch (Exception $exception){}
            $data['am_data'] = json_encode($data['am_data']);
            $data['pm_data'] = json_encode($data['pm_data']);
            if($result){
//                FDB::query('update ' . 'report_day_channel_detail set pv=' . $data['pv'] . ',uv='. $data['uv'].',total_order='.$data['total_order'].',order_ratio='. $data['order_ratio'] .',conversion='.$data['conversion'] . ',success_order=success_order+'.$data['success_order'] . ',total_money=total_money+'.$data['total_money'] . ' where id='.$result['id']);
                try{
                    $dataTable->where(array('id'=>$result['id']))->update($data);
                }catch (Exception $exception){}
            }else{
                $dataTable->insert($data);
            }
        }
        unset($Data);
        return true;
    }

    /**
     * 通道日总订单数据
     * @param $date
     * @param int $page
     * @param array $Data
     * @param int $page_size
     * @author 93307399@qq.com
     * @return array
     */
    public static function _ChannelOrderDayTotal($date,$Data = array(),$page = 1,$page_size = 300){
        $Table    = new FTable('pay_order','',self::$table);
        $startInt = strtotime($date.' 00:00:00');
        $endInt   = strtotime($date.' 23:59:59');
        $where    = array('create_time' => array('gte' => $startInt, 'lte' => $endInt),);
        $result = array();
        try{
            $result   = $Table->fields('count(id) as total_order,cid')->where($where)->group(array('cid'))->page($page)->limit($page_size)->select();
        }catch (Exception $exception){

        }
        /**
         * 取出批次数据
         */
        foreach ($result as $value){
            $key = $value['cid'];
            if(isset($Data[$key])){
                $Data[$key]['total_order'] += $value['total_order'];
            }else{
                $Data[$key] = array(
                    'total_order' => $value['total_order'],
                    'cid'         => $value['cid'],
                );
            }
        }
        unset($Table,$where,$startInt,$endInt,$value);
        if(count($result) >= $page_size){
            $Data = self::_ChannelOrderDayTotal($date , $Data ,$page + 1 , $page_size);
        }
        unset($result);
        return $Data;
    }

    /**
     * 通道日支付数据
     * @param string $date
     * @param array $Data
     * @param int $page
     * @param int $page_size
     * @author 93307399@qq.com
     * @return array
     */
    public static function _ChannelOrderDayPay($date,$Data = array(),$page = 1,$page_size = 300){
        $Table    = new FTable('pay_order','',self::$table);
        $startInt = strtotime($date.' 00:00:00');
        $endInt   = strtotime($date.' 23:59:59');
//        $set_ID   = array();
        //只取已支付并且未结算的.
        $where    = array('pay_time' => array('gte' => $startInt, 'lte' => $endInt),'status' => 2);
        $result = array();
        try{
            $result   = $Table->fields('count(id) as total_order,cid,sum(money) as total_money')->group(array('cid'))->where($where)->page($page)->limit($page_size)->select();
        }catch (Exception $exception){}
        /**
         * 取出批次数据
         */
        foreach ($result as $value){
            $key = $value['cid'];
            if(isset($Data[$key])){
                $Data[$key]['total_order'] += $value['total_order'];
                $Data[$key]['total_money'] += $value['total_money'];
            }else{
                $Data[$key] = array(
                    'total_order' => $value['total_order'],
                    'total_money' => $value['total_money'],
                    'cid'         => $value['cid'],
                );
            }
        }
        unset($Table,$where,$startInt,$endInt,$value);
        if(count($result) >= $page_size){
            $Data = self::_ChannelOrderDayPay($date , $Data ,$page + 1 , $page_size);
        }
        unset($result);
        return $Data;
    }

    /**
     * 通道小时段数据汇总
     * @param string $date
     * @param array $Data
     * @param int $type 1:上午,2:下午
     * @param int $page
     * @param int $page_size
     * @author 93307399@qq.com
     * @return array
     */
    public static function _ChannelDayAmOrPm($date,$Data = array(),$type = 1,$page = 1,$page_size = 300){
        $Table    = new FTable('pay_order','',self::$table);
        $startInt = strtotime($date.' 00:00:00');
        $endInt   = strtotime($date.' 23:59:59');
        //只取已支付并且未结算的.
        $where    = array('pay_time' => array('gte' => $startInt, 'lte' => $endInt),'status' => 2);
        $result = array();
        try{
            $result   = $Table->fields('count(id) as total_order,cid,sum(money) as total_money,FROM_UNIXTIME(pay_time,\'%H\') as pay_hour')->group(array('cid','pay_time'))->where($where)->page($page)->limit($page_size)->select();
        }catch (Exception $exception){}
        /**
         * 取出批次数据
         */
        foreach ($result as $value){
            $key = $value['cid'] . '_' . $value['pay_hour'];
            if(isset($Data[$key])){
                $Data[$key]['total_order'] += $value['total_order'];
                $Data[$key]['total_money'] += $value['total_money'];
            }else{
                $Data[$key] = array(
                    'total_order' => $value['total_order'],
                    'total_money' => $value['total_money'],
                    'cid'         => $value['cid'],
                    'pay_hour'    => $value['pay_hour'],
                );
            }
        }

        unset($Table,$where,$startInt,$endInt,$value);
        if(count($result) >= $page_size){
            $Data = self::_ChannelDayAmOrPm($date , $Data ,$type,$page + 1 , $page_size);
        }
        unset($result);
        return $Data;
    }

    /**
     * 通道每日数据汇总
     * @param string $date
     * @author 93307399@qq.com
     * @return bool
     */
    public static function reportChannelDayCount($date = ''){
        $dataTable = new FTable('report_day_channel','',self::$table);
        /**
         * 获取汇总基础数据
         */
        $Table = new FTable('report_day_channel_detail','',self::$table);
        $where = array('date' => $date);
        $result = $Table->fields('sum(total_order) as total_order,sum(success_order) as success_order,sum(total_money) as total_money,sum(am_total) as am_total,sum(am_money) as am_money,sum(pm_total) as pm_total,sum(pm_money) as pm_money')->where($where)->limit(1)->find();

        /**
         * 取出批次数据
         */
        $Data['total_order']   = intval($result['total_order']);
        $Data['success_order'] = intval($result['success_order']);
        $Data['total_money']   = intval($result['total_money']);
        $Data['am_total'] = intval($result['am_total']);
        $Data['am_money'] = intval($result['am_money']);
        $Data['pm_total'] = intval($result['pm_total']);
        $Data['pm_money'] = intval($result['pm_money']);
        $Data['refund_order'] = 0;
        $Data['refund_money'] = 0;
        /**
         * 计算各项比率值
         */
        $Data['date']        = $date;
        $Data['update_time'] = time();
        /**
         * 入库到日汇总报表
         */
        $result = $dataTable->fields('id')->where(array('date'=>$date))->find();
        if($result){
            $dataTable->where(array('id'=>$result['id']))->update($Data);
        }else{
            $dataTable->insert($Data);
        }
        return true;
    }
}