<?php
namespace Xhprof\Client;

class Client{

    private $denominator;
    private $numerator;

    private $project_id;

    private $host;
    private $port;
    private $password;

    private $key_type;  // list / pub-sub
    private $redis_key;

    /**
     * default: 1/1000 rate
     * @param int $denominator
     * @param int $numerator
     */
    public function setCollectRate($denominator = 1000, $numerator = 1) {
        // set collect rate
        $this->denominator = $denominator;
        $this->numerator = $numerator;
    }

    /**
     * @param string $host
     * @param int $port
     * @param string $password
     */
    public function setRedisAddres($host = '127.0.0.1', $port = 6379, $password = '') {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    /**
     * @param $redis_key
     * @param $key_type string list / pub-sub
     */
    public function setRedisKeyInfo($redis_key = '', $key_type = 'list') {
        $this->key_type = $key_type;
        $this->redis_key = $redis_key;
    }

    /**
     * @param $project_id
     */
    public function setProjectId($project_id) {
        $this->project_id = $project_id;
    }

    /**
     * run collection
     */
    public function collection() {
        if(!$this->check()) {
            return false;
        }

        $num = mt_rand(0, $this->denominator);
        if($num == $this->numerator) {
            $this->startCollect();
            $this->endCollect();
        }
        return true;
    }

    /**
     * startCollect
     */
    private function startCollect() {
        xhprof_enable(XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_CPU | XHPROF_FLAGS_NO_BUILTINS);
    }

    /**
     * endCollect
     */
    private function endCollect() {
        register_shutdown_function(function() {
            $data = $this->getDumpData();

            try{
                $redis = new Redis();
                if(!$redis->connect($this->host, $this->port)) {
                    return false;
                }
                if($this->key_type == 'list') {
                    $redis->rPush($this->redis_key, json_encode($data));
                } else {
                    $redis->publish($this->redis_key, json_encode($data));
                }
            } catch(Exception $e) {
                return false;
            }
            return true;
        });
    }

    /**
     * @return bool
     */
    private function check() {
        if($this->denominator < 0 || $this->numerator < 0 || ($this->denominator < $this->numerator)) {
            return false;
        }

        if(empty($this->project_id)) {
            return false;
        }

        if(empty($this->host) || empty($this->port)) {
            return false;
        }

        if(empty($this->key_type) || empty($this->redis_key)) {
            return false;
        }
        if(!in_array($this->key_type, ['list', 'pub-sub'])) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    private function getDumpData() {
        $xhprof_data = xhprof_disable();

        $uri = $_SERVER['REQUEST_URI'];
        $time = $_SERVER['REQUEST_TIME'];
        $remote_addr = $_SERVER['REMOTE_ADDR'];
        $data = [
            'method' => $this->getReqMethod(), // 1 get 2 post 3 ajax
            'url' => $uri,                     // request uri
            'ip' => $remote_addr,              // client ip
            'req_time' => $time,               // request time
            'project_id' => $this->pid,        // project id
            'metadata' => $xhprof_data         // xhprof date
        ];
        return $data;
    }

    /**
     * @return int
     */
    private function getReqMethod() {
        //ajax
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            return 3;
        }

        //get post
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            return 1;
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return 2;
        } else {
            return 0;
        }
    }

}