<?php

/**
 * Created by PhpStorm.
 * User: jtp
 * Date: 16-12-19
 * Time: 上午12:19
 */
class Service_Simhash{

    private static  $hash_bits = 64;

    private $hash = NULL;

    public function __construct($text,$type = 1){
        $this->hash = $this->calcHash($text,$type);
    }

    public function getHash(){
        return $this->hash;
    }

    public function __toString(){
        return $this->hash;
    }

    protected function calcHash($text,$type = 1){
        $vector = array_fill(0, self::$hash_bits, 0);
        $extra = new Services_TextExtractor();
        $tokens = $extra->tokenize($text,$type);
        foreach($tokens as $token){
            if(trim($token) == '') {
                continue;
            }
            $token_hex = md5($token);
            $token_bin = '';
            foreach(range(0, strlen($token_hex)-1) as $i){
                $token_bin .= sprintf('%04s', decbin(hexdec($token_hex[$i])));
            }
            foreach(range(0, self::$hash_bits-1) as $i){
                if($token_bin[$i] == '1'){
                    $vector[$i]++;
                }else{
                    $vector[$i]--;
                }
            }
        }
        $fingerprint = str_pad('', self::$hash_bits, '0');
        foreach(range(0, self::$hash_bits-1) as $i){
            if($vector[$i] >= 0){
                $fingerprint[$i] = '1';
            }
        }
        return $fingerprint;
    }

    /**
     * 比较两个文字的相似度.
     * @param string $text
     * @param string $text2
     * @param int $type
     * @author 93307399@qq.com
     * @return float|int
     */
    public static function similarity($text,$text2,$type = 1){
        $text_hash  = new Service_Simhash($text,$type);
        $text2_hash = new Service_Simhash($text2,$type);
        $hash1_bin = $text_hash->getHash();
        $hash2_bin = $text2_hash->getHash();
        $hash1_dec = bindec($hash1_bin);
        $hash2_dec = bindec($hash2_bin);
        if($hash1_dec > $hash2_dec){
            return $hash2_dec / $hash1_dec;
        }else{
            return $hash1_dec / $hash2_dec;
        }
    }
}
