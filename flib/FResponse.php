<?php


/**
 * Class FResponse
 */
class FResponse
{

    /**
     * header
     * @var array
     */
    protected $header = array();

    /**
     * 设字符集，如果设置过 Content-type 为 json, 返回false
     *
     * @param string $encoding
     *
     * @return bool
     */
    public function setCharacterEncoding($encoding = 'utf-8')
    {

        // json 不设编码
        if ($this->header['Content-type'] == 'application/json') return false;

        $this->setHeader('Content-type', 'text/html; charset=' . $encoding);
        return true;
    }


    /**
     * 设置 header
     *
     * @param $headerKey
     * @param $headerValue
     */
    public function setHeader($headerKey, $headerValue)
    {
        $this->header[$headerKey] = $headerValue;
    }

    public function setContentType($contentType)
    {

        if ($contentType == 'json') {
            $this->setHeader('Content-type', 'application/json');
        }
    }

    /**
     * 文本输出内容
     *
     * @param $content string 内容
     */
    public function write($content = null)
    {
        ob_clean();

        foreach ($this->header as $h_key => $h_value) {
            header("{$h_key}: $h_value");
        }

        if ($content) echo $content;
    }

    /**
     * 输出内容，可以是数组，可以是文本
     *
     * @param $mix
     * @return bool
     */
    public static function output($mix)
    {
        global $_F;

        $response = new self;

        if (is_array($mix)) {
            $response->setContentType('json');

            if ($_F['debug']) {
                $mix['debug_info'] = $_F['debug_info'];
            }

            $response->write(json_encode($mix));;
        } elseif (is_string($mix)) {
            $response->write($mix);
        }

        return true;
    }

    public static function sendHeader($headerKey, $headerValue = null)
    {

        if (is_numeric($headerKey) && $headerValue == null) {
            self::sendStatusHeader($headerKey);
        } else {
            header($headerKey . ': ' . $headerValue);
        }
    }

    /**
     * 发送HTTP状态
     *
     * @param integer $code 状态码
     *
     * @return void
     */
    public static function sendStatusHeader($code)
    {
        static $httpStatusMap = array(
            // Success 2xx
            200 => 'OK',
            // Redirection 3xx
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ', // 1.1
            // Client Error 4xx
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            // Server Error 5xx
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        );

        if (isset($httpStatusMap[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $httpStatusMap[$code]);
            // 确保FastCGI模式下正常
            header('Status:' . $code . ' ' . $httpStatusMap[$code]);
        }
    }

    /**
     * 跳转
     *
     * @param string $url
     * @param string $target
     * @return bool
     */
    public static function redirect($url, $target = '')
    {
        global $_F;
        if ($url == 'r') {
            $url = $_SERVER ['HTTP_REFERER'];
        }

        if ($_F ['in_ajax']) {
            self::output(array('code' => 301, 'url' => $url, 'target' => $target));
            exit;
        }

        if ($target == 301) {
            self::sendStatusHeader(301);
            self::sendHeader('Location', $url); // 跳转到新地址
        } elseif ($target) {
            echo "<script> {$target}.location.href = '{$url}'; </script>";
        } else {
            header("location: " . $url);
        }

        exit;
    }

    /**
     * 刷新页面
     */
    public static function refresh()
    {
        self::redirect('r');
    }

    /**
     * 返回内容JSON格式。
     *
     * @param int $code
     * @param string $msg
     * @param array $res
     * @return bool
     */
    public static function outputJSON($code = -1, $msg = '', $res = array())
    {
        global $_F;

        if ($_F['debug']) {
            $res['debug_info'] = $_F['debug_info'];
        }

        $json['code'] = $code;
        $json['status'] = $code == 0 ? 'ok' : 'error';

        if (strlen($msg) != 0) {
            $json['msg'] = $msg;
        } else {
            if ($json['code'] > 0 && $json['code'] < 1000) {
                $msgMap = array(
                    1 => '请求参数有误',
                    2 => '会员已被禁用',
                    3 => '会员已被删除',
                    4 => '会员没有找到',
                    5 => '不是vip',
                    6 => '头像未审核通过',
                    7 => '性别不符',
                    8 => '手机未验证',
                    9 => '资料不完善',
                    10 => '头像未上传',
                    11 => '目标不能是自己',
                    12 => '没有填写手机号'
                );

                $json['msg'] = $msgMap[$json['code']];
            }
        }

        if ($res != null) {
            $json['res'] = $res;
        }

//        $json['res'] = ($res == null ? array() : $res);

        $response = new self;
        $response->setContentType('json');
        $response->write(json_encode($json));;

        return true;
    }
}
