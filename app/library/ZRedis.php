<?php
/**
 * User: zhaosuji
 * Email : zhaosuji@foxmail.com
 * Date: 2017/4/17 15:40
 */
use \Phalcon\Mvc\User\Component;

class ZRedis extends Component
{
    protected static $_instance = null;
    protected $_redobj = null;

    public function __construct()
    {
        $this->getObj();
    }

    public static function getIns()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function getObj()
    {
        if ($this->_redobj == null) {
            $this->_redobj = new Redis();
            $this->_redobj->pconnect($this->config->redis_server->host, $this->config->redis_server->port);
        }
        return $this->_redobj;
    }

    public function set($key, $value, $time = 0)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        if(is_array($value)){
            $value = json_encode($value);
        }
        if (!$this->_redobj->set($key, $value, $time)) {
            return false;
        }
        return true;
    }

    public function get($key)
    {
        if (empty($key)) {
            return false;
        }
        return $this->_redobj->get($key);
    }

    public function del($key)
    {
        if (empty($key)) {
            return false;
        }
        if (!$this->_redobj->del($key)) {
            return false;
        }
        return true;
    }

    public function rename($oldKey, $newKey)
    {
        if (empty($oldKey) || empty($newKey)) {
            return false;
        }
        $this->_redobj->rename($oldKey,$newKey);
    }

}