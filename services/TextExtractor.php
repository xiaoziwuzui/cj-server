<?php

/**
 * Created by phpstorm
 * @name 分词相关封装功能
 * @author xiaojiang432524@163.com
 * @time 2018/5/12
 * @version 0.1
 */

class Services_TextExtractor
{

    public $fix_f    = ', . ， 。 、 : ： ? ？ ! ！ ; ； + - / *';

    public $fix_unit = '吧,罢,呗,啵,的,价,家,啦,来,唻,了,嘞,哩,咧,咯,啰,喽,吗,嘛,嚜,么,哪,呢,呐,否,呵,哈,不,兮,般,则,连,罗,给,噻,哉,呸,也,故,以,呀,而,阿,盖,且,然,其,乎,与,哇,夫,啊,虽,维,惟,斯,还,就,但,是,人,你,我,他,在,和,大,小,个,着,为,一个,一样,一次成,得了,而已,的话,来着,了得,也罢,已而,着呢,着哩,着呐,来的,也好,便了,起见,就是,似地,是的,再说,不过,呃,欸,耶,哟,欤,呕,噢,呦,嘢,跟,既,同,及,况,况且,何况,乃至,乃,便,于是,然后,至于,说到,此外,像,如,比方,接着,却,虽然,但是,然而,偏偏,只如,好比,如同,似乎,等于,不如,不及,与其,可是,固然,尽管,纵然,不但,不仅,而且,并,不管,以免,为了,非常,绝对,极度,十分,最,顶级,太,更,极其,格外,分外,一直,才,总,全部,总体,总共,共,统统,又,仅仅,只,光,一概,已经,曾经,早已,刚刚,正,正在,就要,将然,居然,竟然,究竟,这里,那里,每一处,上,有';

    public function formatTitle($string){
        //去除html标签
        $string = strip_tags($string);
        //过滤掉空格
        $string = str_replace(' ','',$string);
        //过滤标点符号
        $string = preg_replace('/(\d+)/','',$string);

//        $string = str_replace(',','',$string);
//        $string = str_replace('.','',$string);
//        $string = str_replace('，','',$string);
//        $string = str_replace('。','',$string);
//        $string = str_replace('、','',$string);
//        $string = str_replace(':','',$string);
//        $string = str_replace('：','',$string);
//        $string = str_replace('?','',$string);
//        $string = str_replace('？','',$string);
//        $string = str_replace('!','',$string);
//        $string = str_replace('！','',$string);
//        $string = str_replace(';','',$string);
//        $string = str_replace('；','',$string);

        $string = str_replace(array('“', '”', '《', '》', '&nbsp;', '&quot;', '(', ')', '（', '）', '.','…','—','~',),'',$string);
        return $string;
    }

    public function formatText($text){
        /**
         * 去除html标签
         */
        $text = strip_tags($text);
        //过滤掉空格
        $text = str_replace(chr(13),'',$text);
        $text = str_replace(chr(10),'',$text);
        $text = str_replace(' ','',$text);
        $text = str_replace('&nbsp;','',$text);
        $text = str_replace('.',',',$text);
        $text = str_replace('。',',',$text);
        $text = str_replace('，',',',$text);
        $text = str_replace('！',',',$text);
        $text = str_replace('、',',',$text);
        $text = str_replace('!',',',$text);
        return $text;
    }

    public function tokenize($text,$type = 1){
        global $_F;
        if($type == 2){
            $text = $this->formatText($text);
            $outText = explode(',',$text);
        }else if($type == 3){
            $text = $this->formatTitle($text);
            Service_Analysis::$loadInit = false;
            $Analysis = new Service_Analysis('utf-8', 'utf-8', false);
            $Analysis->LoadDict();
            $Analysis->SetSource($text);
            $Analysis->differFreq = true;
            $Analysis->differMax = true;
            $Analysis->unitWord = true;
            $Analysis->StartAnalysis(true);
            $outText = $Analysis->GetFinallyKeywords(30);
            $outText = explode(',',$outText);
            $Analysis->__destruct();
        }else{
            $text = $this->formatTitle($text);
            Service_Analysis::$loadInit = false;
            $Analysis = new Service_Analysis('utf-8', 'utf-8', false);
            $Analysis->LoadDict();
            $Analysis->SetSource($text);
            $Analysis->differFreq = true;
            $Analysis->differMax = true;
            $Analysis->unitWord = true;
            $Analysis->StartAnalysis(true);
            $outText = $Analysis->GetFinallyResult('{|}', false);
            $outText = explode('{|}',$outText);
//            $outText = $Analysis->GetFinallyKeywords(15);
//            $outText = explode(',',$outText);
            $Analysis->__destruct();
        }
        /**
         * 过滤掉新词
         */
        $result   = array();
        $is_open  = false;
        $fix_word = array();
        foreach (explode(' ',$this->fix_f) as $unit){
            $fix_word[$unit] = 1;
        }
        foreach (explode(',',$this->fix_unit) as $unit){
            $fix_word[$unit] = 1;
        }
        foreach ($outText as $v){
            if(trim($v) == '') {
                continue;
            }
            if($v == '('){
                $is_open = true;
            }
            if($v == ')'){
                $is_open = false;
                continue;
            }
            if($is_open === true){
                continue;
            }
            if(isset($fix_word[$v])){
                continue;
            }
            $result[] = $v;
        }
        if($_F['run_in'] == 'shell' && $_F['dev_mode'] == true){
//            echo implode(',',$result),chr(10);
        }
        return $result;
    }

}