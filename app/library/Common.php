<?php
use Phalcon\Mvc\User\Component as Component;
use Phalcon\Logger\Adapter\File as FileAdapter;

class Common extends Component
{
    /**
     * 返回错误结果
     * @param $code
     * @param string $errMsg
     * @return string
     */
    public function _printError($code, $errMsg = '')
    {
        $out = $this->errmsg->getErrMsg($code, $errMsg);

        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }


    /**
     * @param array $value
     * @param int $isobj
     * @return string
     */
    public function _printSuccess($value = array(), $isobj = 0)
    {
        $out = array("status" => 1, "data" => $value);

        if ($isobj) {
            $out = array("state" => 1, "data" => (object)$value);
        }

        return json_encode($out);
    }

    /**
     * 记录日志
     * @param $abbr
     * @param $type
     * @param $message
     */
    public function _logger($abbr, $type, $message)
    {
        $filePath = ERRLOG_PATH . "$abbr.log";
        if (defined('DEBUG_MODEL') || defined('TRACE')) {
            //判断日志文件是否存在，不存在则创建
            if (!is_dir(ERRLOG_PATH)) {
                mkdir(ERRLOG_PATH, 0777, true);
            } elseif (!file_exists($filePath)) {
                touch($filePath);
            }

            $logger = new FileAdapter($filePath);
            $common_data = '[' . json_encode($_REQUEST) . ']';
            $message = $message . '  ' . $common_data;
            switch ($type) {
                case 'D':  # Debug
                    $logger->debug($message);
                    break;

                case 'I':  # Info
                    $logger->info($message);
                    break;

                case 'N':  # Notice
                    $logger->notice($message);
                    break;

                case 'W':  # Warning
                    $logger->warning($message);
                    break;

                case 'E':  # Error
                    $logger->error($message);
                    break;

                case 'C':  # Critical
                    $logger->critical($message);
                    break;

                case 'A':  # Alert
                    $logger->alert($message);
                    break;

                case 'M':  # Emergency
                    $logger->emergency($message);
                    break;
            }
            return;
        }

        $ctname = "timer4$abbr";  # commit timer name
        $lgname = "logger$abbr";  # logger name
        $coname = "reopen$abbr";  # close-open timer name

        $cts = time(); # current timestatmp

        if (!$this->persistent->has($ctname)) {
            # Open+a log file
            $logger = new FileAdapter($filePath);

            # Put handler into bag
            $this->persistent->set($lgname, $logger);

            # Start log transaction
            $this->persistent->get($lgname)->begin();

            # Put next-reopen-logfile timestamp into bag
            $this->persistent->set($coname, mktime(3, 0, 0, (int)date('m', strtotime("+1 day")),
                (int)date('d', strtotime("+1 day")),
                (int)date('Y', strtotime("+1 day"))));

            # Put commit timestamp into bag - here first time
            $this->persistent->set($ctname, $cts);
        }

        switch ($type) {
            case 'D':  # Debug
                $this->persistent->get($lgname)->debug($message);
                break;

            case 'I':  # Info
                $this->persistent->get($lgname)->info($message);
                break;

            case 'N':  # Notice
                $this->persistent->get($lgname)->notice($message);
                break;

            case 'W':  # Warning
                $this->persistent->get($lgname)->warning($message);
                break;

            case 'E':  # Error
                $this->persistent->get($lgname)->error($message);
                break;

            case 'C':  # Critical
                $this->persistent->get($lgname)->critical($message);
                break;

            case 'A':  # Alert
                $this->persistent->get($lgname)->alert($message);
                break;

            case 'M':  # Emergency
                $this->persistent->get($lgname)->emergency($message);
                break;
        }

#       echo "last commit time = " . $this->persistent->get($ctname) . "</br>";
#       echo "next reopen time = " . $this->persistent->get($coname) . "</br>";
#       echo "current time     = " . $cts . "</br>";

        if ($cts - $this->persistent->get($ctname) >= 5) {
            # Commit
            $this->persistent->get($lgname)->commit();

            # Refresh the commit timestamp in bag
            $this->persistent->set($ctname, $cts);

            # Whether it is time to close-open
            if ($cts >= $this->persistent->get($coname)) {
                # Close and rename
                $this->persistent->get($lgname)->close();
                rename($filePath,
                    ERRLOG_PATH . "$abbr" . date('Ymd') . ".log");

                # Open and put handler into bag
                $logger = new FileAdapter($filePath);
                $this->persistent->set($lgname, $logger);

                # Put next-reopen-logfile timestamp into bag
                $this->persistent->set($coname, mktime(3, 0, 0, (int)date('m', strtotime("+1 day")),
                    (int)date('d', strtotime("+1 day")),
                    (int)date('Y', strtotime("+1 day"))));
            }

            $this->persistent->get($lgname)->begin();
        }

        return;
    }

    /**
     * 读结果缓存文件
     *
     * @params  string  $cache_name
     * @return  array   $data
     */
    function readStaticCache($cacheName)
    {
        static $result = array();
        if (!empty($result[$cacheName])) {
            return $result[$cacheName];
        }

        $data = array();
        $staticCachesDir = ROOT_PATH . '/temp/static_caches/';
        if (!is_dir($staticCachesDir)) {

        }

        $cacheFilePath = $staticCachesDir . $cacheName . '.php';

        if (file_exists($cacheFilePath)) {
            include_once($cacheFilePath);
            $result[$cacheName] = $data;
            return $result[$cacheName];
        } else {
            return false;
        }
    }

    /**
     * 写结果缓存文件
     *
     * @params  string  $cache_name
     * @params  string  $caches
     *
     * @return
     */
    function writeStaticCache($cacheName, $caches)
    {
        $cacheFilePath = ROOT_PATH . '/temp/static_caches/' . $cacheName . '.php';
        $content = "<?php\r\n";
        $content .= "\$data = " . var_export($caches, true) . ";\r\n";
        $content .= "?>";
        file_put_contents($cacheFilePath, $content, LOCK_EX);
    }


    /**
     * 通过新浪API将长URL转换成短URL
     * @param    $longUrl
     * @return   mixed
     */
    public function sinaLongUrlToShortUrl($longUrl)
    {
        $this->config = new ConfigIni(CONFIG_PATH . 'config-msg.ini');
        $apiKey = $this->config->app->SinaAppKey ? $this->config->app->SinaAppKey : '1462195281';
        $apiUrl = 'http://api.t.sina.com.cn/short_url/shorten.json?source=' . $apiKey . '&url_long=' . urlencode($longUrl);
        //初始化一个curl对象
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, $apiUrl);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        $response = curl_exec($curlObj);
        curl_close($curlObj);
        $json = json_decode($response);

        return ($json[0]->url_short);
    }

    public function uploadImgs($files)
    {
        $rep = array();
        foreach ($files as $file) {
            $fileError = $file->getError();//文件是否错误
            $fileName = $file->getName();//文件名
            $fileExt = $file->getExtension();//文件后缀名
            $fileKey = $file->getKey();//文件的key
            $fileSize = $file->getSize();//文件大小

            if ($fileSize > 1024 * 1024) {
                $rep['fail'][] = array("name" => $fileKey, "url" => $fileName);
                continue;
            }

            $filePath = $this->common->getImgSavePath($fileExt);
            if (!$filePath) {
                $rep['fail'][] = array("name" => $fileKey, "url" => $filePath);
                continue;
            }

            $file->moveTo(PUBLIC_PATH . $filePath);
            $rep['success'][] = array("name" => $fileKey, "url" => $filePath);
        }

        return $rep;
    }


    /**
     * 获取文件存储路径
     * @param $fileExt
     * @return bool|string
     */
    public function getImgSavePath($fileExt)
    {
        $vedioExtArr = array('amr', 'wav', 'mp3', 'mp4', 'acc', 'ape', 'rm', 'ogg', 'avi', 'wma');
        $imgExtArr = array('jpg', 'jpeg', 'png', 'gif');

        $curDirName = $this->getDateByCurrentWeek();

        if (in_array(strtolower($fileExt), $imgExtArr)) {
            $relativeDirPath = 'media/images/' . $curDirName;
        } elseif (in_array(strtolower($fileExt), $vedioExtArr)) {
            $relativeDirPath = 'media/vedio/' . $curDirName;
        } else {
            return false;
        }

        $dirPath = PUBLIC_PATH . $relativeDirPath;
        if (!is_dir($dirPath)) {
            $rusult = mkdir($dirPath, 0777, true);
            if (!$rusult) {
                return false;
            }
        }
        $fileName = date("dHis", time()) . rand(1000, 10000) . "." . $fileExt;
        $relativeFilePath = $relativeDirPath . DS . $fileName;

        return $relativeFilePath;
    }


    /**
     * 获取当前星期某一天的日期
     * @param int $specifyWeek
     * @return mixed
     */
    public function getDateByCurrentWeek($specifyWeek = 0)
    {
        $curWeek = date('w');
        if ($curWeek == $specifyWeek) {
            $curDirName = date("Ymd");
        } else {
            $curDirName = date("Ymd", strtotime("-$curWeek days"));
        }

        return $curDirName;
    }


    /**
     * 返回指定日期当天起始时间戳
     * @param $date 20160102
     * @return int
     */
    public function dayStampStart($date)
    {
        $pattern = "/[^0-9]/";
        $date = trim(preg_replace($pattern, '', $date));
        $stampStart = strtotime($date . "000000");
        return $stampStart;
    }

    /**
     * 返回指定日期当天结束时间戳
     * @param $date 20160102
     * @return int
     */
    public function dayStampEnd($date)
    {
        $pattern = "/[^0-9]/";
        $date = trim(preg_replace($pattern, '', $date));
        $stampEnd = mktime(23, 59, 59, substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4));
        return $stampEnd;
    }

    public function stringFormaToCamelCase($string)
    {
        $tempArr = explode('_', $string);
        $convertedStr = '';
        foreach ($tempArr as $world) {
            $convertedStr .= ucfirst($world);
        }

        return $convertedStr;
    }

    /**
     * convert a string to array
     * eg. 1,2,3;4,5,6;  array(array(1,2,3),array(4,5,6))
     * @date 20151204
     * @author zhangy@hcxdi.com
     */
    public function strToArr($value)
    {
        if (!is_string($value))
            return false;

        $allValues = array();

        $value = trim(str_replace(' ', '', $value), ';');
        if (substr_count($value, ';')) {
            $itemArr = explode(';', $value);
            foreach ($itemArr AS $item) {
                $tempArr = explode(',', $item);
                $allValues[] = $tempArr;
            }
        } else {
            $allValues = array(explode(',', $value));
        }

        return $allValues;
    }


    /**
     * @param $url
     * @param string $type
     * @param string $res
     * @param string $arr
     * @return mixed
     */
    public function httpCurl($url, $type = 'get', $res = 'json', $arr = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        if ($res == 'json') {
            return json_decode($output, true);
        }

        return $output;
    }


    /**
     * Get Client Ip
     * @return string
     */
    function getClientIp()
    {
        $ip = 'unknown';
        $pattern = '/((?:(?:25[0-5]|2[0-4]\d|[01]?\d?\d)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d?\d))/';
        if (isset($_SERVER['REMOTE_ADDR']) && preg_match($pattern, $_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_FORWARDED_FOR']) && preg_match($pattern, $_SERVER['HTTP_X_REAL_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_REAL_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match($pattern, $_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match($pattern, $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return $ip;
    }

    /**
     * 私钥加密
     * @param  mix $plaintxt 需要加密的数组或字符
     * @param  string $key
     * @return string           返回加密后的字符串
     */
    public function rsaPrivateEncrypt($plaintxt, $key = '')
    {
        if ($key == '') {
            $filename = KEY_PATH . 'rsa_private_key.pem';
            $key = @file_get_contents($filename);
            openssl_pkey_get_private($key);
        }
        $encrypted = '';

        if (is_array($plaintxt)) {
            $plaintxt = http_build_query($plaintxt);
        }
        openssl_private_encrypt($plaintxt, $encrypted, $key);
        $encrypted = base64_encode($encrypted);
        //$encrypted = str_replace('+','%2B',$encrypted);

        return urlencode($encrypted);
    }


    /**
     * 公钥解密
     * @param  string $encrypttxt 需要解密的字符
     * @param  string $key
     * @return array              解密后返回的数组
     */
    public function rsaPublicDecrypt($encrypttxt, $key = '')
    {
        if ($key == '') {
            $filename = KEY_PATH . 'rsa_public_key.pem';
            $key = @file_get_contents($filename);
            $key = openssl_pkey_get_public($key);
        }

        $plaintxt = urldecode($encrypttxt);
        //$plaintxt = str_replace('%2B','+',$plaintxt);
        $plaintxt = base64_decode($plaintxt);
        openssl_public_decrypt($plaintxt, $encrypted, $key);

        parse_str($encrypted, $return);
        return $return;
    }


    /**
     * 获取指定时间内的日期列表
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function dateListInSpecifiedInterval($startDate, $endDate)
    {
        $startDateStamp = $this->dayStampStart($startDate);
        $endDateStamp = $this->dayStampEnd($endDate);

        $dateListArr = array();
        for ($i = $startDateStamp; $i <= $endDateStamp; $i += 86400) {
            $dateListArr[] = date("Y-m-d", $i);
        }

        return $dateListArr;
    }

    /**
     * 判断变量是否定义
     * @param $param
     * @return bool
     */
    public function setOrEmpty($param)
    {
        if (!empty($param) && isset($param)) {
            return true;
        }
        return false;
    }

    /**
     * 生成短信验证码
     * @param int $codeLength 验证码长度
     * @return string
     */
    public function generateVerifyCode()
    {
        $codeLength = isset($this->config->cloud_message_config->codeLength) ? $this->config->cloud_message_config->codeLength : 4;
        $str = '1234567890';
        $code = '';
        for ($i = 0; $i < $codeLength; $i++) {
            $code .= $str{mt_rand(0, 9)};
        }
        return $code;
    }

    /**
     * token生成
     */
    public function generateToken()
    {
        $key = 'change';
        $str = $key . time();
        $token = md5(md5($str));
        return $token;
    }

    /**
     * 主键ID生成
     */
    public function generateGuid()
    {
        $key = 'agent';
        $str = $key . time();
        $guid = md5(md5($str));
        return $guid;
    }


    /**
     * 二维数组根据某个字段排序
     * @param $arrUsers
     * @param $field
     * @param string $direction
     * @return array
     */
    public function multiAarraySort($arrUsers, $field, $direction = 'SORT_DESC')
    {
        $arrSort = array_column($arrUsers, $field);
        if (count($arrSort) !== count($arrUsers)) {
            return array();
        }

        array_multisort($arrSort, constant($direction), $arrUsers);

        return $arrUsers;
    }

    /**
     * 密码加密
     * @param $salt
     * @param $password
     * @return mixed
     */
    public function encryptPassWord($salt, $password)
    {
        $url = $this->config->api->pwdEncryptURL;
        $arr = array(
            'salt' => $salt,
            'password' => $password
        );
        $password = $this->common->httpCurl($url, 'get', '', $arr);
        return $password;
    }

    /**
     *  生成16位密码盐
     */
    public function generateSalt()
    {
        $str = substr(md5('agentQJ' . time()), -16);
        return $str;
    }
}