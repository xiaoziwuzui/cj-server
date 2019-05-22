<?php
/**
 * User: xiaojiang432524@163.com
 * Date: 2019/02/26
 * 渠道单日数据报表脚本
 */
define('APP_ROOT', dirname(dirname(dirname(__FILE__))) . '/');
define('FLIB_RUN_MODE', 'manual');
define('PUBLIC_ROOT', APP_ROOT . 'public/');
define('UPLOAD_ROOT', APP_ROOT . 'public/uploads/');

require_once APP_ROOT . "flib/Flib.php";

set_time_limit(0);

global $_F;
$cache_key = 'report_day_channel';
if($_SERVER['USER'] == 'jiangtaiping'){
    $_F['dev_mode'] = true;
    FCache::delete($cache_key);
}
FCache::delete($cache_key);
$d_cache = FCache::get($cache_key);

if($d_cache > 0){
    echo 'is run',chr(10);
    exit(0);
}else{
    FCache::set($cache_key,2,360);
}

$run_start_time = date('Y-m-d H:i:s');
FLogger::write("StartTime: {$run_start_time}\n", 'report_day_channel');
if ($argv[1]) {
    $report_date = $end_date = $argv[1];
    if ($report_date == '-1' || $report_date == '-2' || $report_date == '-3') {
        $report_date = $end_date = date('Y-m-d', strtotime('-1 day'));
        if ($argv[1] == '-2') {
            if ($argv[2]) {
                $report_date = $end_date = $argv[2];
            }
        }
        if ($argv[1] == '-3') {
            if ($argv[2]) {
                $report_date = $argv[2];
            }
            if ($argv[3]) {
                $end_date = $argv[3];
            }
        }
    }
} else {
    $report_date = $end_date = date('Y-m-d');
}

$report_date = strtotime($report_date);
$end_date    = strtotime($end_date);

if($report_date > $end_date){
    $maxDate = $report_date;
    $minDate = $end_date;
}else{
    $maxDate = $end_date;
    $minDate = $report_date;
}
for($time = $minDate;$time <= $maxDate;$time += 86400){
    $day = date('Y-m-d',$time);
    echo $day,chr(10);
    /**
     * 渠道日详细数据更新
     */
    Services_Settlement::reportChannelDayDetail($day);
    /**
     * 渠道日汇总数据更新
     */
    Services_Settlement::reportChannelDayCount($day);
}
FCache::delete($cache_key);
echo 'success',chr(10);