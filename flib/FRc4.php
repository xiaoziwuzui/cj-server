<?php

/**
 * rc4 加密解密
 * Created by PhpStorm.
 * User: WYR
 * Date: 2015/11/10
 * Time: 16:22
 */
class FRc4
{
    var $key;

    function FRc4($key)
    {
        $this->key = $key;
    }

    /** 加密
     * @param $input 需加密字符串
     */
    function encrypt($input)
    {
        return base64_encode($this->rc4($this->key, $input));
    }

    /** 解密
     * @param $input 需加密字符串
     */
    function decrypt($input)
    {
        $input = base64_decode($input);
        return $this->rc4($this->key, $input);
    }

    /**
     * @param $pwd 密钥
     * @param $data 需加密字符串
     * @return string
     */
    function rc4($pwd, $data)
    {
        $key[] = "";
        $box[] = "";

        $pwd_length = strlen($pwd);
        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($pwd[$i % $pwd_length]);
            $box[$i] = $i;
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        $cipher = '';
        for ($a = $j = $i = 0; $i < $data_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;

            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $k = $box[(($box[$a] + $box[$j]) % 256)];
            $cipher .= chr(ord($data[$i]) ^ $k);
        }

        return $cipher;
    }
}