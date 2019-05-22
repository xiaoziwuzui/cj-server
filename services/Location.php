<?php

class Service_Location
{
    public function  getList($lat, $lng, $sex, $limit, $page = 1)
    {

        if ($sex == 1) {
            $sex = 'm';
        } else {
            $sex = 'f';
        }

//        $url = "http://192.168.1.87:11222/adjacent?num={$limit}&lat={$lat}&lng={$lng}&sex={$sex}";
        $url = "http://192.168.2.75:11222/adjacent?num={$limit}&lat={$lat}&lng={$lng}&sex={$sex}&page={$page}";
        $content = json_decode(FHttp::get($url), true);


        if ($content['items']) {
            return $content['items'];
        } else {
            return array();
        }
    }

    public function getListUsers($lat, $lng, $sex, $limit, $page = 1)
    {
        $users = $this->getList($lat, $lng, $sex, $limit, $page);

        return $users;
    }

    /**
     * 上报经纬度
     * @param $uid int 用户ID
     * @param $lat string 纬度
     * @param $lng string 经度
     * @param $sex int 性别
     * @return bool
     */
    public static function updateUserLocation($uid, $lat, $lng, $sex)
    {

        if ($sex == 1) {
            $sex = 'm';
        } elseif ($sex == 2) {
            $sex = 'f';
        } else {
            return false;
        }


        if ($uid && $lat > 1 && $lng > 1) {
//            $url = "http://192.168.1.87:11222/report?id={$uid}&lat={$lat}&lng={$lng}&sex={$sex}";
            $url = "http://192.168.2.75:11222/report?id={$uid}&lat={$lat}&lng={$lng}&sex={$sex}";
            FHttp::get($url);
        }
        return true;
    }

//    public static function getProvinceTextById($provinceId) {
//
//    }

    /**
     * 通过经纬度取地址
     * @param string $x
     * @param string $y
     * @return string
     */
    public static function getRealAddress($x, $y)
    {
        if ($x && $y) {
            $arr = self::getAddressFromXY($x, $y);
            return $arr['province'] . $arr['city'];
        } else {
            return '';
        }
    }

    /**
     * 通过百度api取得地址
     * @param string $x
     * @param string $y
     * @return array|null
     */
    public static function getAddressFromXY($x, $y)
    {
        $cache_key = "getAddressFromXY_{$x}_{$y}";
        $place_arr = FCache::get($cache_key);
        if (!$place_arr) {
            try {
                //http://api.map.baidu.com/geocoder/v2/?location=28.207876,112.886129&output=json&ak=3da6e7ccd407395106fc19d6770613b2
                $url = "http://api.map.baidu.com/geocoder/v2/?location={$y},{$x}&output=json&ak=3da6e7ccd407395106fc19d6770613b2";
                $json_place = file_get_contents($url);
                $place_arr_list = json_decode($json_place, true);
                $place_arr = array(
                    'province' => $place_arr_list['result']['addressComponent']['province'],
                    'city' => $place_arr_list['result']['addressComponent']['city']
                );


//                $address = $place_arr['result']['addressComponent']['province'] . $place_arr['result']['addressComponent']['city'];
//                return $address;
            } catch (Exception $ex) {
//                return array();
                $place_arr = array(
                    'province' => '',
                    'city' => ''
                );
            }

            FCache::set($cache_key, $place_arr, 30 * 24 * 3600);
        }

        return $place_arr;
    }

    /**
     * 根据经纬度取城市信息
     * @param string $x
     * @param string $y
     * @return array
     */
    public static function getIpLocationFromXY($x, $y)
    {
        if ($x && $y) {
            $data = self::getAddressFromXY($x, $y);
//            FLogger::write($data, 'quickRegXy');
//            FLogger::write($x . '-' . $y, 'quickRegXy');
            $region_id = Service_City::getProvinceIdByName($data['province']);
            $city_id = Service_City::getCityIdByName($region_id, $data['city']);
            $arr = array(
                'code' => 0,
                'data' => array(
                    "region" => $data['province'],
                    "region_id" => $region_id,
                    "city" => $data['city'],
                    "city_id" => $city_id,
                )
            );
//            FLogger::write($arr, 'quickRegXy');
        } else {
            $arr = array(
                'code' => 0,
                'data' => array(
                    "region" => "",
                    "region_id" => 0,
                    "city" => "",
                    "city_id" => 0,
                )
            );
        }

        return $arr;

    }


    /**
     * 获取用户列表
     * @return bool|mixed|null|string
     */
    public static function getUserXYList()
    {
        $cache_key = 'getUserXY_from_coordinates';
        $arr = FCache::get($cache_key);
        if (!$arr) {
            $filename = APP_ROOT . "/lib/coordinates/1.js";
            $handle = fopen($filename, "r"); //读取二进制文件时，需要将第二个参数设置成'rb'

            //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
            $contents = fread($handle, filesize($filename));

            $arr = json_decode($contents, true);

            fclose($handle);

            FCache::set($cache_key, $arr, 30 * 24 * 3600);
        }

        return $arr;
    }

    /**
     * 根据地区获取x,y坐标
     * @param $region_id int 省份ID
     * @param $city_id int 城市ID
     * @return array
     */
    public static function getUserXY($region_id, $city_id)
    {

        $user_detail = array(
            'x' => 0,
            'y' => 0
        );
        $arr = self::getUserXYList();
        foreach ($arr as $v) {
            if ($region_id == 0 && $city_id == 0) {
                $user_detail = array(
                    'x' => $v['x'],
                    'y' => $v['y']
                );
                break;
            }


            if ($v['region_id'] == $region_id && $v['city_id'] == $city_id) {
                $user_detail = array(
                    'x' => $v['x'],
                    'y' => $v['y']
                );
                break;
            }


            if ($v['region_id'] == $region_id && $v['region_id'] == $v['city_id']) {
                $user_detail = array(
                    'x' => $v['x'],
                    'y' => $v['y']
                );
                break;
            }


            if ($v['region_id'] == $region_id && $city_id == 0) {
                $user_detail = array(
                    'x' => $v['x'],
                    'y' => $v['y']
                );
                break;
            }

        }

        if ($user_detail['x'] == 0 && $user_detail['y'] == 0) {
            foreach ($arr as $v) {
                if ($v['region_id'] == $region_id) {
                    $user_detail = array(
                        'x' => $v['x'],
                        'y' => $v['y']
                    );
                    break;
                }
            }
        }

        return $user_detail;
    }

    /**
     * gps坐标转百度坐标
     * @param $x 112.8814
     * @param $y 28.21121
     * @return array
     */
    public static function getBaiDuLocationXY($x, $y)
    {
        $retData = array('x' => 0, 'y' => 0);
        $url = "http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x={$x}&y={$y}";
        $html = FHttp::get($url);
        if ($html) {
            $data = json_decode($html, true);
            if ($data['error'] == '0') {
                $retData = array('x' => base64_decode($data['x']), 'y' => base64_decode($data['y']));
            }
        }

        return $retData;
    }
}