<?php

/**
 * Created by PhpStorm.
 * User: wyr
 * Date: 15-1-29
 * Time: 下午5:04
 */
class Service_Oss
{

    private $oss_sdk_service;

    public function __construct()
    {
        /**
         * 加载sdk包以及错误代码包
         */
        require_once APP_ROOT . '/lib/oss/sdk.class.php';

        $this->oss_sdk_service = new ALIOSS();

//设置是否打开curl调试模式
        $this->oss_sdk_service->set_debug_mode(FALSE);


////设置开启三级域名，三级域名需要注意，域名不支持一些特殊符号，所以在创建bucket的时候若想使用三级域名，最好不要使用特殊字符
////$oss_sdk_service->set_enable_domain_style(TRUE);
//
//        /**
//         * 测试程序
//         * 目前SDK存在一个bug，在文中如果含有-&的时候，会出现找不到相关资源
//         */
//        try {
//            /**
//             * Service相关操作
//             */
//            //get_service($oss_sdk_service);
//
//            /**
//             * Bucket相关操作
//             */
//            //create_bucket($oss_sdk_service);
//            //delete_bucket($oss_sdk_service);
//            //set_bucket_acl($oss_sdk_service);
//            //get_bucket_acl($oss_sdk_service);
//
//            //set_bucket_logging($oss_sdk_service);
//            //get_bucket_logging($oss_sdk_service);
//            //delete_bucket_logging($oss_sdk_service);
//
//            //set_bucket_website($oss_sdk_service);
//            //get_bucket_website($oss_sdk_service);
//            //delete_bucket_website($oss_sdk_service);
//
//            /**
//             * 跨域资源共享(CORS)
//             */
//            //set_bucket_cors($oss_sdk_service);
//            //get_bucket_cors($oss_sdk_service);
//            //delete_bucket_cors($oss_sdk_service);
//            //options_object($oss_sdk_service);
//
//            /**
//             * Object相关操作
//             */
//            list_object($oss_sdk_service);
//            //create_directory($oss_sdk_service);
//            //upload_by_content($oss_sdk_service);
//            //upload_by_file($oss_sdk_service);
//            //copy_object($oss_sdk_service);
//            //get_object_meta($oss_sdk_service);
//            //delete_object($oss_sdk_service);
//            //delete_objects($oss_sdk_service);
//            //get_object($oss_sdk_service);
//            //is_object_exist($oss_sdk_service);
//            //upload_by_multi_part($oss_sdk_service);
//            //upload_by_dir($oss_sdk_service);
//            //batch_upload_file($oss_sdk_service);
//
//            /**
//             * 外链url相关
//             */
//            //get_sign_url($oss_sdk_service);
//
//        } catch
//        (Exception $ex) {
//            die($ex->getMessage());
//        }
    }


    public function Service_Oss()
    {
        $this->__construct();
    }

    /**
     * 函数定义
     */
    /*%**************************************************************************************************************%*/
// Service 相关

//获取bucket列表
    public function get_service()
    {
        $response = $this->oss_sdk_service->list_bucket();
        $this->_format($response);
    }

    /*%**************************************************************************************************************%*/
// Bucket 相关

//创建bucket
    public function create_bucket($bucket)
    {
        //$acl = ALIOSS::OSS_ACL_TYPE_PRIVATE;
        $acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ;
        //$acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE;

        $response = $this->oss_sdk_service->create_bucket($bucket, $acl);
        $this->_format($response);
    }

//删除bucket
//        $bucket = 'phpsdk1349849369';
    public function delete_bucket($bucket)
    {
        $response = $this->oss_sdk_service->delete_bucket($bucket);
        $this->_format($response);
    }

//设置bucket ACL
//        $bucket = 'phpsdk1349849394';
    public function set_bucket_acl($bucket)
    {
        $acl = ALIOSS::OSS_ACL_TYPE_PUBLIC_READ_WRITE;
        $response = $this->oss_sdk_service->set_bucket_acl($bucket, $acl);
        $this->_format($response);
    }

//获取bucket ACL
//$bucket = 'phpsdk1349849394';
//$options = array(
//ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
//);
    public function get_bucket_acl($bucket, $options)
    {
        $response = $this->oss_sdk_service->get_bucket_acl($bucket, $options);
        $this->_format($response);
    }

//设置bucket logging
//$bucket = 'phpsdk1349849394';
//$target_bucket = 'backet2';
//$target_prefix = 'test';
    public function  set_bucket_logging($bucket, $target_bucket, $target_prefix)
    {

        $response = $this->oss_sdk_service->set_bucket_logging($bucket, $target_bucket, $target_prefix);
        $this->_format($response);
    }

//获取bucket logging
//$bucket = 'phpsdk1349849394';
    public function  get_bucket_logging($bucket)
    {
        $response = $this->oss_sdk_service->get_bucket_logging($bucket);
        $this->_format($response);
    }

//删除bucket logging
//$bucket = 'phpsdk1349849394';
    public function  delete_bucket_logging($bucket)
    {
        $response = $this->oss_sdk_service->delete_bucket_logging($bucket);
        $this->_format($response);
    }

//设置bucket website
//$bucket = 'phpsdk1349849394';
//$index_document = 'index.html';
//$error_document = 'error.html';
    public function  set_bucket_website($bucket, $index_document, $error_document)
    {
        $response = $this->oss_sdk_service->set_bucket_website($bucket, $index_document, $error_document);
        $this->_format($response);
    }

//获取bucket website
//$bucket = 'phpsdk1349849394';
    public function  get_bucket_website($bucket)
    {


        $response = $this->oss_sdk_service->get_bucket_website($bucket);
        $this->_format($response);
    }

//删除bucket website
//$bucket = 'phpsdk1349849394';
    public function  delete_bucket_website($bucket)
    {


        $response = $this->oss_sdk_service->delete_bucket_website($bucket);
        $this->_format($response);
    }

    /*%**************************************************************************************************************%*/
//跨域资源共享(CORS)

//设置bucket cors
//$bucket = 'phpsdk1349849394';
//
//$cors_rule[ALIOSS::OSS_CORS_ALLOWED_HEADER] = array("x-oss-test");
//$cors_rule[ALIOSS::OSS_CORS_ALLOWED_METHOD] = array("GET");
//$cors_rule[ALIOSS::OSS_CORS_ALLOWED_ORIGIN] = array("http://www.b.com");
//$cors_rule[ALIOSS::OSS_CORS_EXPOSE_HEADER] = array("x-oss-test1");
//$cors_rule[ALIOSS::OSS_CORS_MAX_AGE_SECONDS] = 10;
    public function  set_bucket_cors($bucket, $cors_rule)
    {

        $cors_rules = array($cors_rule);

        $response = $this->oss_sdk_service->set_bucket_cors($bucket, $cors_rules);
        $this->_format($response);
    }

//获取bucket cors
//$bucket = 'phpsdk1349849394';
    public function  get_bucket_cors($bucket)
    {
        $response = $this->oss_sdk_service->get_bucket_cors($bucket);
        $this->_format($response);
    }

//删除bucket cors
//$bucket = 'phpsdk1349849394';
    public function  delete_bucket_cors($bucket)
    {
        $response = $this->oss_sdk_service->delete_bucket_cors($bucket);
        $this->_format($response);
    }

//options object
//$bucket = 'phpsdk1349849394';
//$object = '1.jpg';
//$origin = 'http://www.b.com';
//$request_method = 'GET';
//$request_headers = 'x-oss-test';
    public function  options_object($bucket, $object, $origin, $request_method, $request_headers)
    {
        $response = $this->oss_sdk_service->options_object($bucket, $object, $origin, $request_method, $request_headers);
        $this->_format($response);
    }

    /*%**************************************************************************************************************%*/
// Object 相关

//获取object列表
//$bucket = 'efrwerwertyrty';
//$options = array(
//'delimiter' => '/',
//'prefix' => '',
//'max-keys' => 10,
//    //'marker' => 'myobject-1330850469.pdf',
//);
    public function list_object($bucket, $options)
    {
        $response = $this->oss_sdk_service->list_object($bucket, $options);
        $this->_format($response);
    }

//创建目录
//$bucket = 'efrwerwertyrty';
//    //$dir = '"><img src=\"#\" onerror=alert(\/';
//$dir = 'myfoll////';
    public function create_directory($bucket, $dir)
    {
        $response = $this->oss_sdk_service->create_object_dir($bucket, $dir);
        $this->_format($response);
    }

//通过内容上传文件
    public function upload_by_content($bucket, $object, $imgUrl)
    {


        $upload_file_options = array(
            'content' => fopen($imgUrl, 'r'),
            'length' => filesize($imgUrl),
            ALIOSS::OSS_HEADERS => array(
                'Expires' => date('Y-m-d H:i:s'),
            ),
        );

        $response = $this->oss_sdk_service->upload_file_by_content($bucket, $object, $upload_file_options);

        return $response;
        //_format($response);
    }

//通过路径上传文件
//$bucket = 'phpsdk1349849394';
//$object = 'netbeans-7.1.2-ml-cpp-linux.sh';
//$file_path = "D:\\TDDOWNLOAD\\netbeans-7.1.2-ml-cpp-linux.sh";
    public function upload_by_file($bucket, $object, $file_path, $content_type)
    {
        $response = $this->oss_sdk_service->upload_file_by_file($bucket, $object, $file_path, $content_type);
        if ($response->status == 200) {
            return true;
        } else {
            FLogger::write($response, 'update_mumu_oss');
            return false;
        }
//        $this->_format($response);
    }

//拷贝object
//$from_bucket = 'invalidxml';
//$from_object = '&#26;&#26;_100.txt';
//$to_bucket = 'invalidxml';
//$to_object = '&#26;&#26;_100.txt';
//$options = array(
//'content-type' => 'application/json',
//);
    public function copy_object($from_bucket, $from_object, $to_bucket, $to_object, $options)
    {
        //copy object


        $response = $this->oss_sdk_service->copy_object($from_bucket, $from_object, $to_bucket, $to_object, $options);
        $this->_format($response);
    }

//获取object meta
//$bucket = 'invalidxml';
//$object = '&#26;&#26;_100.txt';
    public function get_object_meta($bucket, $object)
    {


        $response = $this->oss_sdk_service->get_object_meta($bucket, $object);
        $this->_format($response);
    }

//删除object
//$bucket = 'invalidxml';
//$object = '&#26;&#26;_100.txt';
    function delete_object($bucket, $object)
    {
        $response = $this->oss_sdk_service->delete_object($bucket, $object);
//        $this->_format($response);
    }

//删除objects
//        $bucket = 'phpsdk1349849394';
//        $objects = array('myfoloder-1349850940/', 'myfoloder-1349850941/',);
    public function delete_objects($bucket, $objects)
    {
        $options = array(
            'quiet' => false,
            //ALIOSS::OSS_CONTENT_TYPE => 'text/xml',
        );

        $response = $this->oss_sdk_service->delete_objects($bucket, $objects, $options);
        $this->_format($response);
    }

//获取object
//        $bucket = 'phpsdk1349849394';
//        $object = 'netbeans-7.1.2-ml-cpp-linux.sh';
//        $filepath = "d:\\cccccccccc.sh";
    public function get_object($bucket, $object, $filepath)
    {

        $options = array(
            ALIOSS::OSS_FILE_DOWNLOAD => $filepath,
            //ALIOSS::OSS_CONTENT_TYPE => 'txt/html',
        );

        $response = $this->oss_sdk_service->get_object($bucket, $object, $options);
        $this->_format($response);
    }

//检测object是否存在
//        $bucket = 'phpsdk1349849394';
//        $object = 'netbeans-7.1.2-ml-cpp-linux.sh';
    public function is_object_exist($bucket, $object , $return = false)
    {
        $response = $this->oss_sdk_service->is_object_exist($bucket, $object);
        if($return){
            return ($response->status == '200') ? true : false;
        }
        return $this->_format($response);
    }

//通过multipart上传文件
//        $bucket = 'phpsdk1349849394';
//        $object = 'Mining.the.Social.Web-' . time() . '.pdf'; //英文
//        $filepath = "D:\\Book\\Mining.the.Social.Web.pdf"; //英文
    public function upload_by_multi_part($bucket, $object, $filepath)
    {
        $options = array(
            ALIOSS::OSS_FILE_UPLOAD => $filepath,
            'partSize' => 5242880,
        );

        $response = $this->oss_sdk_service->create_mpu_object($bucket, $object, $options);
        $this->_format($response);
    }

//通过multipart上传整个目录
//        $bucket = 'phpsdk1349849394';
//        $dir = "D:\\alidata\\www\\logs\\aliyun.com\\oss\\";
//        $recursive = false;
    public function upload_by_dir($bucket, $dir, $recursive)
    {
        $response = $this->oss_sdk_service->create_mtu_object_by_dir($bucket, $dir, $recursive);
        var_dump($response);
    }

//通过multi-part上传整个目录(新版)
//        $options = array(
//            'bucket' => 'phpsdk1349849394',
//            'object' => 'picture',
//            'directory' => 'D:\alidata\www\logs\aliyun.com\oss',
//        );
    public function batch_upload_file($options)
    {
        $response = $this->oss_sdk_service->batch_upload_file($options);
    }


    /*%**************************************************************************************************************%*/
// 签名url 相关

//生成签名url,主要用户私有权限下的访问控制
//        $bucket = 'phpsdk1349849394';
//        $object = 'netbeans-7.1.2-ml-cpp-linux.sh';
//        $timeout = 3600;
    public function get_sign_url($bucket, $object, $timeout = 3600)
    {


        $response = $this->oss_sdk_service->get_sign_url($bucket, $object, $timeout);
        var_dump($response);
    }

    /*%**************************************************************************************************************%*/
// 结果 相关

//格式化返回结果
    public function _format($response)
    {
        echo '|-----------------------Start---------------------------------------------------------------------------------------------------' . "\n";
        echo '|-Status:' . $response->status . "\n";
        echo '|-Body:' . "\n";
        echo $response->body . "\n";
        echo "|-Header:\n";
        print_r($response->header);
        echo '-----------------------End-----------------------------------------------------------------------------------------------------' . "\n\n";
    }


}