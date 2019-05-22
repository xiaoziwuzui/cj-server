<?php

class Service_Lottery
{
    /**
     * @desc 拼凑，获得Prob和Alias数组
     * @param array $data
     * @param array $prob
     * @param array $alias
     */
    public static function init(array $data, array &$prob, array &$alias) {
        $length = count($data);
        $small  = $large = array();
        for ($i = 0; $i < $length; ++$i) {
            // 扩大倍数，使每列高度可为1
            $data[$i] = $data[$i] * $length;
            // 分到两个数组，便于组合
            if ($data[$i] < 1) {
                $small[] = $i;
            } else {
                $large[] = $i;
            }
        }

        // 将超过1的色块与原色拼凑成1
        while (!empty($small) && !empty($large)) {
            $n_index = array_shift($small);
            $a_index = array_shift($large);
            $prob[$n_index] = $data[$n_index];
            $alias[$n_index] = $a_index;
            // 重新调整大色块
            $data[$a_index] = ($data[$a_index] + $data[$n_index]) - 1;
            if ($data[$a_index] < 1) {
                $small[] = $a_index;
            } else {
                $large[] = $a_index;
            }
        }

        // 剩下大色块都设为1
        while (!empty($large)) {
            $n_index = array_shift($large);
            $prob[$n_index] = 1;
        }

        // 一般是精度问题才会执行这一步
        while (!empty($small)) {
            $n_index = array_shift($small);
            $prob[$n_index] = 1;
        }
    }

    /**
     * @desc 获取某种物品
     * @param array $prob
     * @param array $alias
     * @return int
     */
    public static function generation($prob, $alias) {
        $length = count($prob) - 1;
        // 假设最小的几率是万分之一
        $MAX_P  = 100000;
        // 抛出硬币
        $coin_toss = rand(1, $MAX_P) / $MAX_P;
        // 随机落在一列
        $col = rand(0, $length);
        // 判断是否落在原色
        $b_head = ($coin_toss < $prob[$col]) ? TRUE : FALSE;
        return $b_head ? $col : $alias[$col];
    }
}

$data = array(0.25, 0.2, 0.1, 0.05, 0.4);
$prob = $alias = array();

Service_Lottery::init($data, $prob, $alias);
$result = Service_Lottery::generation($prob, $alias);

$count = array(0, 0, 0, 0, 0);
for ($i = 0; $i < 10000; $i++) {
    $result = Service_Lottery::generation($prob, $alias);
    $count[$result]++;
}
echo '<pre>';
print_r($count);
echo '</pre>';