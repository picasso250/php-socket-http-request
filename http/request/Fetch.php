<?php

namespace http\request;

class Fetch
{
    public function doGet($host, $url = '/', $cb = null, $time_limit = 5) {
        return $this->do('GET', $host, $url, $cb, $time_limit);
    }
    
    public function do($method, $host, $url = '/', $cb = null, $time_limit = 5)
    {
        $t = microtime(true);
        $lines = array(
            "$method $url HTTP/1.1",
            "Host: $host",
            "Content-Length: 73",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36",
            "Referer: http://$host/",
            "Accept-Language: zh-CN,zh;q=0.8",
        );
        $text = implode("\r\n", $lines);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            throw new \Exception("Could not create socket", 1);
        }
        $connection = socket_connect($socket, $host, $port);
        if (!$connection) {
            throw new \Exception("Could not connet server", 1);
        }
        self::write_enough($socket);
        $str = '';
        while (($buf = socket_read($socket, 1024)) !== '' && (microtime(true) - $t <= $time_limit)) {
            $str .= $buf;
            if ($cb) {
                if (!$cb($buf, $str)) {
                    break;
                }
            }
        }

        return $str;
    }


    /**
     * 读取足够的字节数
     * @param $socket
     * @param $len
     * @return string
     */
    protected static function read_enough($socket, $len)
    {
        if (!$len) {
            return '';
        }
        $ret = socket_read($socket, $len);
        while (strlen($ret) != $len && $len > 0) {
            echo "read more\n";
            $len -= strlen($ret);
            $ret .= socket_read($socket, $len);
        }
        return $ret;
    }

    /**
     * 写入足够的字节
     * @param $socket
     * @param $str
     * @return bool
     */
    protected static function write_enough($socket, $str)
    {
        $length = strlen($str);
        while (true) {
            $sent = socket_write($socket, $str, $length);
            if ($sent === false) {
                return false;
            }
            // Check if the entire message has been send
            if ($sent < $length) {
                // If not sent the entire message.
                // Get the part of the message that has not yet been send as message
                $str = substr($str, $sent);
                // Get the length of the not send part
                $length -= $sent;
            } else {
                break;
            }
        }
        return true;
    }
}
