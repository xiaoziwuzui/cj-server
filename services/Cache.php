<?php

/**
 * Created by phpstorm
 * @name Services_Cache 相关缓存方法
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */
class Services_Cache
{
    /**
     * @var string $cache_fix 缓存前缀
     */
    public static $cache_fix = 'plate_v1_';

    /**
     * 获取商户列表
     * @throws Exception
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getMerchants($update = false){
        $cache_key = self::$cache_fix . 'merchants';
        $Result = FCache::get($cache_key);
        if (!$Result || $update === true) {
            $table = new FTable('merchants');
            $Result = $table->fields('uid,truename,real_name')->where(array('status'=>1))->order(array('uid' => 'asc'))->select();
            FCache::set($cache_key,$Result);
            unset($table,$cache_key);
        }
        return $Result;
    }

    /**
     * 获取商户列表KV版
     * @throws Exception
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getMerchantsKV($update = false){
        $result = self::getMerchants($update);
        $array = array();
        foreach ($result as $v){
            $array[$v['uid']] = $v;
        }
        return $array;
    }

    /**
     * 配置缓存
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getConfig($update = false){
        $cache_key = self::$cache_fix . '_cfg';
        $Result = FCache::get($cache_key);
        if (!$Result || $update === true) {
            $table = new FTable('system_config');
            try{
                $Result = $table->fields('data')->where(array('item'=>'setting'))->find();
            }catch (Exception $exception){

            }
            if(!$Result){
                $Result = array();
                $config = Service_Public::formatConfig(FConfig::get('setting'));
                foreach ($config as $K=>$v){
                    $Result[$K] = $v['value'];
                }
                $data = array(
                    'item' => 'setting',
                    'data' => json_encode($Result)
                );
                $table->insert($data);
                unset($config);
            }else{
                $Result = json_decode($Result['data'],true);
            }
            FCache::set($cache_key,$Result);
            unset($table);
        }
        unset($cache_key);
        return $Result;
    }


    /**
     * 获取所有数据统计
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getAllTotal($update = false){
        $result = array(
            'plate'   => 0,
            'pay_order' => 0,
            'pay_money' => 0,
        );
        $cache_key = self::$cache_fix . 'all_t3_';
        $cacheInfo = FCache::get($cache_key);
        if(!$cacheInfo || $update !== false){
            $plateTable = new FTable('plate');
            $orderTable   = new FTable('pay_order');
            $count = array();
            try{
                $count = $plateTable->fields('count(id) as total')->where(array('status'=>array('lt'=>9)))->find();
            }catch (Exception $exception){}
            $result['plate'] = $count['total'];
            try{
                $count = $orderTable->fields('count(id) as total,sum(money) as money')->where(array('status'=>2))->find();
            }catch (Exception $exception){}
            $result['pay_order'] = $count['total'];
            $result['pay_money'] = $count['money'];
            unset($orderTable,$plateTable,$count);
            FCache::set($cache_key,$result,10 * 60);
        }else{
            $result = $cacheInfo;
        }
        return $result;
    }

    /**
     * 获取日汇总报表缓存数据
     * @param $date
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getReportDay($date,$update = false){
        $cache_key = self::$cache_fix . 'day_report_2';
        $result = FCache::get($cache_key);
        if (!$result || $update === true) {
            $table = new FTable('report_day_channel');
            try{
                $result = $table->where(array('date'=>$date))->find();
            }catch (Exception $exception){}
            FCache::set($cache_key,$result);
            unset($table,$cache_key);
        }
        return $result;
    }

    /**
     * 获取月汇总报表缓存数据
     * @param $month
     * @param bool $update
     * @author 93307399@qq.com
     * @return array
     */
    public static function getReportMonth($month,$update = false){
        $cache_key = self::$cache_fix . 'day_month_2';
        $data = FCache::get($cache_key);
        if (!$data || $update === true) {
            $table     = new FTable('report_day_channel');
            $data      = array();
            $result      = array();
            $startTime = $month . '-01';
            $endTime   = date('Y-m-d',strtotime('-1 day',strtotime('+1 month',strtotime($month))));
            try{
                $result    = $table->where(array('date'=>array('gte'=>$startTime,'lte'=>$endTime)))->select();
            }catch (Exception $exception){}
            foreach ($result as $v){
                foreach ($v as $vk=>$vv){
                    if($vk == 'id') continue;
                    if($vk == 'date') continue;
                    $data[$vk] += $vv;
                }
            }
            FCache::set($cache_key,$data);
            unset($table,$cache_key);
        }
        return $data;
    }

    /**
     * 获取广告配置
     * @throws Exception
     * @author 93307399@qq.com
     * @return array
     */
    public static function getAd(){
        global $_F;
        $config  = self::getConfig();
        $path    = $config['cdn_domain'] . 'assets/';
        $action  = $_F['action'];
        $control = str_replace('Controller_','',$_F['controller']);
        $result  = array(
            'banner' => array(),
            'ad'     => array(),
            'userAd' => array(),
        );
        /**
         * 头部背景图片
         */
        $result['banner'] = array(
            'bg'    => '<img src="'.$path . 'home/images/banner.jpg" alt="" />',
//            'float' => '<div class="float-red"><a href="/index.html"><img src="'.$path . 'home/images/redbag.png" alt="" /></a></div>',
        );
        /**
         * 底部广告图片
         */
        if($control == 'Member_Main' && $action == 'index'){
            $result['ad'][] = '<p><a href="https://g.eqxiu.com/s/ilU40CgL" style="margin: 0;"><img alt="" src="'.$path . 'home/images/ccb_ad.jpg" /></a></p>';
        }else{
            $result['ad'][] = '<p><a href="http://www.kcreate.cn/weixin/html/n/2.html" style="margin: 0;"><img alt="" src="'.$path . 'home/images/ad_new.png" /></a></p>';
        }
        if($action == 'success'){
//            $result['ad'][] = '<p><a href="/index.html"><img src="'.$path . 'home/images/card.jpg" alt="" /></a></p>';
        }
        if($control == 'Member_Main'){
            $result['userAd'][] = '<div class="user-ad"><img src="'.$path.'home/images/user-ad.jpg" alt="" /></div>';
        }

        return $result;
    }

}