<?php

namespace http\request;

class Fetch
{
    public function doGet($host, $url = '/', $cb = null, $time_limit = 5) {
        return $this->doRequest('GET', $host, $url, $cb, $time_limit);
    }
    
    public function doRequest($method, $host, $url = '/', $cb = null, $time_limit = 5)
    {
        $t = microtime(true);
        $lines = array(
            "$method $url HTTP/1.1",
            "Host: $host",
            "User-Agent: curl/7",
            "Pragma: no-cache",
            "Accept: */*",
        );
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            throw new \Exception("Could not create socket", 1);
        }
        $port = 80;
        $connection = socket_connect($socket, $host, $port);
        if (!$connection) {
            throw new \Exception("Could not connet server", 1);
        }
        foreach ($lines as $line) {
            echo $line,"\n";
            self::write_enough($socket, $line);
            self::write_enough($socket, "\r\n");
        }
        self::write_enough($socket, "\r\n");
        $str = '';
        $buf = null;
        socket_set_nonblock($socket);
        while (($buf = socket_read($socket, 1024)) !== '' && (($now = microtime(true)) <= $time_limit + $t)) {
            echo "=$now=\t$buf\n";
            if ($buf) {
                $str .= $buf;
            }
            if ($cb && !$cb($buf, $str)) {
                break;
            }
            sleep(1);
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
