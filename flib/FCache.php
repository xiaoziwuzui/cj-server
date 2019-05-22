<?php

/**
 * Class FCache
 */
class FCache
{

    private $_cache_type = null;
    private $_cache_key = '';

    private $_cache_conf = '';

    /**
     * @var Memcache
     */
    private $_memcache_conn = null;

    const CACHE_TYPE_NULL = 0;
    const CACHE_TYPE_REDIS = 1;
    const CACHE_TYPE_MEMCACHE = 2;
    const CACHE_TYPE_FILE = 3;

    /**
     * @var Redis
     */
    private $_redis_conn;

    /**
     * @param int $_cache_type
     *
     * @return FCache
     */
    public static function getInstance($_cache_conf = '', $_cache_type = self::CACHE_TYPE_NULL)
    {
        static $ins = null;

        if ($ins) {
            $ins->_cache_type = $_cache_type;
            $ins->_cache_conf = $_cache_conf;
            return $ins;
        }

        $ins = new self;
        $ins->_cache_type = $_cache_type;
        $ins->_cache_conf = $_cache_conf;
        return $ins;
    }

    /**
     * 连接到指定的配置文件
     * @return bool
     */
    public function connect()
    {

        // 如果 getInstance 中指定了链接
        if ($this->_cache_type == self::CACHE_TYPE_REDIS) {
            $this->_redis_conn = $this->redisConnect();
            return $this->_redis_conn;
        } elseif ($this->_cache_type == self::CACHE_TYPE_MEMCACHE) {
            $this->_memcache_conn = $this->memcacheConnect();
            return $this->_memcache_conn;
        }

        if ($this->_cache_conf) {
            $config_memcache_name = 'cache.memcache_' . $this->_cache_conf . '.enable';
            $config_redis_name = 'cache.redis_' . $this->_cache_conf . '.enable';
        } else {
            $config_memcache_name = 'cache.memcache.enable';
            $config_redis_name = 'cache.redis.enable';
        }

        if (FConfig::get($config_redis_name)) {
            $this->_cache_type = self::CACHE_TYPE_REDIS;
            $this->_redis_conn = $this->redisConnect();
            return $this->_redis_conn;
        } elseif (FConfig::get($config_memcache_name)) {
            $this->_cache_type = self::CACHE_TYPE_MEMCACHE;
            $this->_memcache_conn = $this->memcacheConnect();
            return $this->_memcache_conn;
        } else {
            $this->_cache_type = self::CACHE_TYPE_FILE;
        }
        return false;
    }

    /**
     * @param      $cache_key
     * @param      $cache_content
     * @param int $cache_time
     */
    public function _set($cache_key, $cache_content, $cache_time = 7200)
    {
        $this->_cache_key = $cache_key;

        $this->connect();


        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $this->redisSetCache($cache_key, $cache_content, $cache_time);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $this->memcacheSetCache($cache_key, $cache_content, $cache_time);
                break;
            case self::CACHE_TYPE_FILE:
                $this->fileSetCache($cache_key, $cache_content, $cache_time);
                break;
        }
    }

    /**
     * @param $cache_key
     *
     * @return null
     */
    public function _get($cache_key)
    {
        $this->_cache_key = $cache_key;

        $this->connect();
        $ret = null;
        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $ret = $this->redisGetCache($cache_key);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $ret = $this->memcacheGetCache($cache_key);
                break;
            case self::CACHE_TYPE_FILE:
                $ret = $this->fileGetCache($cache_key);
                break;
        }
        return $ret;
    }

    private function _remove($cache_key)
    {
        $this->_cache_key = $cache_key;

        $this->connect();

        $ret = null;
        switch ($this->_cache_type) {
            case self::CACHE_TYPE_REDIS:
                $this->redisRemoveCache($cache_key);
                break;
            case self::CACHE_TYPE_MEMCACHE:
                $this->memcacheRemoveCache($cache_key);
                break;
            case self::CACHE_TYPE_FILE:
                $this->fileRemoveCache($cache_key);
                break;
        }

        return $ret;
    }

    /**
     * 清空 cache
     */
    public function _flush()
    {
        global $_F;

        $this->connect();

        if (FConfig::get('global.memcache.enable')) {
            $_F['memcache']->flush();
        }

        // 清除文件缓存
        $cache_dir = dirname(self::getCacheDir() . 'file');
        if (is_dir($cache_dir)) {
            $cache_dir_new = $cache_dir . '.bak_' . $_F['http_host'] . '_' . date('Y-m-d_H_i_s') . rand(1000, 9999);
            rename($cache_dir, $cache_dir_new);
        }
    }

    public function redisConnect()
    {
        static $redis_server_count = 0;
        static $redis_conn = array();
        if ($this->_cache_conf) {
            $config_name = 'cache.redis_' . $this->_cache_conf . '.server';
        } else {
            $config_name = 'cache.redis.server';
        }
        $redis_server_config = FConfig::get($config_name);
        if (!$redis_server_count) {
            $redis_server_count = count($redis_server_config);
        }

        if ($redis_server_count == 1) {
            $server_id = 0;
        } else {
            $server_id = FMisc::str2crc32($this->_cache_key) % $redis_server_count;
        }

        $conf_key = 0;
        if ($this->_cache_conf) {
            $conf_key = $this->_cache_conf;
        }
        if ($redis_conn[$conf_key][$server_id]) {
            return $redis_conn[$conf_key][$server_id];
        }

        $cache_keys = array_keys($redis_server_config);

        $redis = new \Redis();
        if ($redis_server_config[$cache_keys[$server_id]]['conn_type']) {
            $redis->pconnect($redis_server_config[$cache_keys[$server_id]]['ip'],
                $redis_server_config[$cache_keys[$server_id]]['port'],
                $redis_server_config[$cache_keys[$server_id]]['time_out']);
        } else {
            $redis->connect($redis_server_config[$cache_keys[$server_id]]['ip'],
                $redis_server_config[$cache_keys[$server_id]]['port'],
                $redis_server_config[$cache_keys[$server_id]]['time_out']);
        }

        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $redis->select(intval($redis_server_config[$cache_keys[$server_id]]['db']));
        $redis_conn[$conf_key][$server_id] = $redis;
        return $redis_conn[$conf_key][$server_id];
    }

    public function memcacheConnect()
    {
        static $memcache_server_count = 0;
        static $memcache_conn = array();
        if ($this->_cache_conf) {
            $config_name = 'cache.memcache_' . $this->_cache_conf . '.server';
        } else {
            $config_name = 'cache.memcache.server';
        }
        $memcache_server_config = FConfig::get($config_name);
        if (!$memcache_server_count) {
            $memcache_server_count = count($memcache_server_config);

            if (!$memcache_server_count) {
                throw new Exception('config/cache has no memcache server !');
            }
        }

        if ($memcache_server_count == 1) {
            $server_id = 0;
        } else {
            $server_id = FMisc::str2crc32($this->_cache_key) % $memcache_server_count;
        }
        $conf_key = 0;
        if ($this->_cache_conf) {
            $conf_key = $this->_cache_conf;
        }
        if ($memcache_conn[$conf_key][$server_id]) {
            return $memcache_conn[$conf_key][$server_id];
        }

        $cache_keys = array_keys($memcache_server_config);

        $memcache_conn[$conf_key][$server_id] = new Memcache;


        if ($memcache_server_config[$cache_keys[$server_id]]['p_connect']) {
            $memcache_conn[$conf_key][$server_id]->pconnect(
                $memcache_server_config[$cache_keys[$server_id]]['ip'],
                $memcache_server_config[$cache_keys[$server_id]]['port']);
        } else {
            $memcache_conn[$conf_key][$server_id]->connect(
                $memcache_server_config[$cache_keys[$server_id]]['ip'],
                $memcache_server_config[$cache_keys[$server_id]]['port']);
        }


        return $memcache_conn[$conf_key][$server_id];
    }

    public function fileConnect()
    {

    }

    private function redisSetCache($cache_key, $cache_content, $cache_time = 7200)
    {
        if ($cache_content) {
            if ($cache_time) {
                $this->_redis_conn->setex($cache_key, $cache_time, $cache_content);
                //set($cache_key, $cache_content, 0, 0, $cache_time);
            } else {
                $this->_redis_conn->set($cache_key, $cache_content);
            }
        }
    }

    private function redisGetCache($cache_key)
    {
        $value = $this->_redis_conn->get($cache_key);
        return $value;
    }

    private function redisRemoveCache($cache_key)
    {
        $this->_redis_conn->delete($cache_key);
    }

    private function memcacheGetCache($cache_key)
    {
        return $this->_memcache_conn->get($cache_key);
    }

    private function memcacheSetCache($cache_key, $cache_content, $cache_time = 7200)
    {
        if ($cache_time > 86400 * 30) {
            $cache_time = 86400 * 30;
        }
        $this->_memcache_conn->set($cache_key, $cache_content, MEMCACHE_COMPRESSED, $cache_time);
    }

    private function memcacheRemoveCache($cache_key)
    {
        $this->_memcache_conn->delete($cache_key);
    }

    private function fileSetCache($cache_key, $cache_content, $cache_time = 7200)
    {
        $save_content = json_encode(array('cache_time' => $cache_time, 'content' => $cache_content));
        $cache_file = self::getFileFCachePath($cache_key);
        file_put_contents($cache_file, $save_content);
    }

    private function fileRemoveCache($cache_key)
    {
        $cache_file = self::getFileFCachePath($cache_key);
        unlink($cache_file);
    }

    private function _getRedusLink()
    {
        $this->connect();
        return $this->_redis_conn;
    }

    public static function getRedisLink($conf = '')
    {
        return self::getInstance($conf)->_getRedusLink();
    }

    public function fileGetCache($cache_key)
    {
        $cache_file = self::getFileFCachePath($cache_key);
        $content = json_decode(file_get_contents($cache_file), true);
        if ($content && (filemtime($cache_file) + intval($content['cache_time'])) > time()) {
            return $content['content'];
        } else {
            return null;
        }
    }

    /**
     * 设置缓存
     * @param $cache_key
     * @param $cache_content
     * @param int $cache_time 如果用的是memcache，时间最长为30天
     * @param string $conf
     * @author 93307399@qq.com
     */
    public static function set($cache_key, $cache_content, $cache_time = 7200, $conf = '')
    {
        self::getInstance($conf)->_set($cache_key, $cache_content, $cache_time);
    }

    public static function get($cache_key, $conf = '')
    {
        return self::getInstance($conf)->_get($cache_key);
    }

    public static function getFileFCachePath($cache_key)
    {

        $cache_dir = self::getCacheDir();

        $hash_file_path = FFile::getHashPath($cache_key, 3, $cache_dir, true);
        $cache_file = $hash_file_path['file_path'];

        return $cache_file;
    }

    public static function getCacheDir()
    {
        if (!defined("APP_ROOT")) {
            throw new Exception("FCache Exception: APP_ROOT not defined.");
        }
        $cache_dir = FConfig::get('global.cache_dir');
        if ($cache_dir) {
            $cache_dir = "{$cache_dir}/" . md5($cache_dir) . '/';
        } else {
            $cache_dir = APP_ROOT . "data/cache/";
        }

        return $cache_dir;
    }

    public static function delete($cache_key, $conf = '')
    {
        self::getInstance($conf)->_remove($cache_key);
    }

    public static function flush($conf = '')
    {
        self::getInstance($conf)->_flush();
    }

    /**
     * 增加
     * @param $cache_key
     * @param string $conf
     * @param int $offset
     * @param int $time
     * @author 93307399@qq.com
     * @return bool|int
     */
    public static function increment($cache_key, $conf = '', $offset = 1, $time = 7200)
    {
        return self::getInstance($conf)->_increment($cache_key, $offset, $time);
    }

    public function _increment($cache_key, $offset, $time)
    {
        $this->_cache_key = $cache_key;
        $this->connect();
        $ret = $this->_memcache_conn->increment($cache_key, $offset);
        if ($ret === false) {
            $this->_memcache_conn->set($cache_key, 1, MEMCACHE_COMPRESSED, $time);
            $ret = 1;
        }
        return $ret;
    }
}