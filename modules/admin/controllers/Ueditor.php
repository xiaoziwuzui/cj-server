<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2017/2/23
 * Time: 14:57
 */
class Controller_Admin_Ueditor extends Controller_Admin_Abstract
{

    private $ueconfig = array();

    private $fieldName = 'Filedata';

    private $uploadType = 'img';

    private $uploadSize = 4096;

    private $allowFiles = array(".png", ".jpg", ".jpeg", ".gif", ".bmp");

    private $uploadPath = 'upload/{yyyy}{mm}{dd}/{time}{rand:6}';

    public function __construct()
    {
        parent::__construct();
        $this->ueconfig = array(
            /* 上传图片配置项 */
            'imageActionName'=>'uploadimage',
            'imageFieldName'=>'Filedata',
            'imageMaxSize'=>102400000,
            'imageAllowFiles'=>array(".png", ".jpg", ".jpeg", ".gif", ".bmp"),
            'imageCompressEnable'=>false,
            'imageCompressBorder'=>1600,
            'imageInsertAlign'=>'none',
            'imageUrlPrefix'=>'/',
            'imagePathFormat'=>'upload/{yyyy}{mm}{dd}/{time}{rand:6}',
            /* 涂鸦图片上传配置项 */
            'scrawlActionName'=>'uploadscrawl',
            'scrawlFieldName'=>'Filedata',
            'scrawlPathFormat'=>'upload/{yyyy}{mm}{dd}/{time}{rand:6}',
            'scrawlMaxSize'=>102400000,
            'scrawlUrlPrefix'=>'/',
            'scrawlInsertAlign'=>"none",
            /* 截图工具上传 */
            'snapscreenActionName'=>"uploadsnapscreen",
            'snapscreenFieldName'=>"upfile",
            'snapscreenPathFormat'=>"upload/{yyyy}{mm}{dd}/{time}{rand:6}",
            'snapscreenUrlPrefix'=>'/',
            'snapscreenInsertAlign'=>"none",
            /* 抓取远程图片配置 */
            'catcherLocalDomain'=>array("127.0.0.1", "localhost", "img.baidu.com"),
            'catcherActionName'=>'catchimage',
            'catcherFieldName'=>'Filedata',
            'catcherPathFormat'=>'upload/{yyyy}{mm}{dd}/{time}{rand:6}',
            'catcherUrlPrefix'=>'/',
            'catcherMaxSize'=>102400000,
            'catcherAllowFiles'=>array(".png", ".jpg", ".jpeg", ".gif", ".bmp"),
            /* 上传视频配置 */
            'videoActionName'=>'uploadvideo',
            'videoFieldName'=>'upfile',
            'videoPathFormat'=>'upload/{yyyy}{mm}{dd}/{time}{rand:6}',
            'videoUrlPrefix'=>'/',
            'videoMaxSize'=>102400000,
            'videoAllowFiles'=>array(".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"),
            /* 上传文件配置 */
            'fileActionName'=>'uploadfile',
            'fileFieldName'=>'upfile',
            'filePathFormat'=>'upload/{yyyy}{mm}{dd}/{time}{rand:6}',
            'fileUrlPrefix'=>'/',
            'fileMaxSize'=>51200000,
            'fileAllowFiles'=>array(".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"),
            /* 列出指定目录下的图片 */
            'imageManagerActionName'=>'listimage',
            'imageManagerListPath'=>'upload/',
            'imageManagerListSize'=>20,
            'imageManagerUrlPrefix'=>'/',
            'imageManagerInsertAlign'=>'none',
            'imageManagerAllowFiles'=>array(".png", ".jpg", ".jpeg", ".gif", ".bmp"),
            /* 列出指定目录下的文件 */
            'fileManagerActionName'=>'listfile',
            'fileManagerListPath'=>'upload/',
            'fileManagerUrlPrefix'=>'/',
            'fileManagerListSize'=>20,
            'fileManagerAllowFiles'=>array(
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
            )
        );
        header("Content-Type: text/html; charset=utf-8");
    }

    /**
     * 返回UE编辑器的默认配置
     */
    public function configAction(){
        echo json_encode($this->ueconfig);
    }

    public function uploadimageAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['imagePathFormat'],
            "maxSize" => $this->ueconfig['imageMaxSize'],
            "allowFiles" => $this->ueconfig['imageAllowFiles']
        );
        $fieldName = $this->ueconfig['imageFieldName'];
        $up = new Service_Ueditor($fieldName, $config, 'upload');
        echo json_encode($up->getFileInfo());
    }

    public function uploadsnapscreenAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['snapscreenPathFormat'],
            "maxSize" => $this->ueconfig['imageMaxSize'],
            "allowFiles" => $this->ueconfig['imageAllowFiles']
        );
        $fieldName = $this->ueconfig['snapscreenFieldName'];
        $up = new Service_Ueditor($fieldName, $config, 'upload');
        echo json_encode($up->getFileInfo());
    }

    public function uploadscrawlAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['scrawlPathFormat'],
            "maxSize" => $this->ueconfig['scrawlMaxSize'],
            "allowFiles" => $this->ueconfig['imageAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $this->ueconfig['scrawlFieldName'];
        $up = new Service_Ueditor($fieldName, $config, 'base64');
        echo json_encode($up->getFileInfo());
    }

    public function uploadvideoAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['videoPathFormat'],
            "maxSize" => $this->ueconfig['videoMaxSize'],
            "allowFiles" => $this->ueconfig['videoAllowFiles']
        );
        $fieldName = $this->ueconfig['videoFieldName'];
        $up = new Service_Ueditor($fieldName, $config, 'upload');
        echo json_encode($up->getFileInfo());
    }

    public function uploadfileAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['filePathFormat'],
            "maxSize" => $this->ueconfig['fileMaxSize'],
            "allowFiles" => $this->ueconfig['fileAllowFiles']
        );
        $fieldName = $this->ueconfig['fileFieldName'];
        $up = new Service_Ueditor($fieldName, $config, 'upload');
        echo json_encode($up->getFileInfo());
    }

    public function catchimageAction(){
        $config = array(
            "pathFormat" => $this->ueconfig['catcherPathFormat'],
            "maxSize" => $this->ueconfig['catcherMaxSize'],
            "allowFiles" => $this->ueconfig['catcherAllowFiles'],
            "oriName" => "remote.png"
        );
        $fieldName = $this->ueconfig['catcherFieldName'];

        /* 抓取远程图片 */
        $list = array();
        if (isset($_POST[$fieldName])) {
            $source = $_POST[$fieldName];
        } else {
            $source = $_GET[$fieldName];
        }
        foreach ($source as $imgUrl) {
            $item = new Service_Ueditor($imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list, array(
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                "source" => $imgUrl
            ));
        }

        /* 返回抓取数据 */
        echo json_encode(array(
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
        ));
    }

    public function listimageAction(){
        $allowFiles = $this->ueconfig['imageManagerAllowFiles'];
        $listSize = $this->ueconfig['imageManagerListSize'];
        $path = $this->ueconfig['imageManagerListPath'];
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            echo json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
            exit(0);
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        /* 返回数据 */
        echo json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));
    }

    public function listfileAction(){
        $allowFiles = $this->ueconfig['fileManagerAllowFiles'];
        $listSize = $this->ueconfig['fileManagerListSize'];
        $path = $this->ueconfig['fileManagerListPath'];
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            echo json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
            exit(0);
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }

        /* 返回数据 */
        echo json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));
    }

    private function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }
}