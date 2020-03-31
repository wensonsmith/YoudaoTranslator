<?php

/**
 * 生词本功能
 * Class WordBook
 */
class WordBook
{
    /**
     * 登录地址
     */
    const LOGIN_URL = 'https://logindict.youdao.com/login/acc/login';

    /**
     * 生词本添加地址
     */
    const ADD_WORD_URL = 'http://dict.youdao.com/wordbook/ajax?action=addword&le=eng&q=';

    /**
     * Cookie 文件
     */
    const COOKIE_FILE = './cookie';

    /**
     * @var
     */
    private $username;
    /**
     * @var
     */
    private $password;

    /**
     * WordBook constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        // 如果cookie文件不存在，先新建一个新文件
        // 否则curl无法保存cookie
        if (!file_exists(self::COOKIE_FILE)) {
            file_put_contents(self::COOKIE_FILE, '');
        }
    }

    /**
     * @param string $word 单词
     * @param string $phonetic 发音
     * @param string $desc 释义
     */
    public function add($word, $phonetic, $desc)
    {
        if ($this->pushWord($word, $phonetic, $desc)) {
            echo $word . ' 已加入生词本';
        } else {
            if ($this->login()) {
                if ($this->pushWord($word, $phonetic, $desc)) {
                    echo $word . ' 已加入生词本';
                } else {
                    echo '添加到生词本失败';
                }
            } else {
                echo '登录失败，请检查用户名和密码';
            }
        }
    }


    /**
     * 登录
     * @return bool
     */
    private function login()
    {
        $response = $this->request(self::LOGIN_URL, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $this->buildHeader(),
            CURLOPT_POSTFIELDS => http_build_query($this->buildForm())
        ]);

        list($header) = explode("\r\n\r\n", $response, 1);

        $matches = [];

        preg_match_all('/Set-Cookie:(?<cookie>.*)\b/m', $header, $matches);

        $cookie = $matches['cookie'];

        if (count($cookie) === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $word
     * @param $phonetic
     * @param $desc
     * @return bool
     */
    private function pushWord($word, $phonetic, $desc)
    {

        $query = $word;
        $tags = 'Alfred';

        $word = compact('word', 'phonetic', 'desc', 'tags');

        $header = $this->buildHeader();
        $header[] = 'Referer:http://dict.youdao.com/wordbook/wordlist';

        $response = $this->request(self::ADD_WORD_URL . $query, [
            CURLOPT_HTTPHEADER => $header
        ]);

        $result = explode("\r\n\r\n", $response, 2);

        return $result[1] == '{"message":"adddone"}';

    }

    /**
     * 请求头
     * @return array
     */
    private function buildHeader()
    {
        return [
            'User-Agent:Mozilla/5.0 (Macintosh Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36',
            'Content-Type:application/x-www-form-urlencoded',
            'Cache-Control:no-cache',
            'Accept:*/*',
            'Connection:Keep-Alive',
        ];
    }

    /**
     * 登录表单
     * @return array
     */
    private function buildForm()
    {
        return [
            'app' => 'web',
            'tp' => 'urstoken',
            'cf' => 3,
            'fr' => 1,
            'ru' => 'http://dict.youdao.com/wordbook/wordlist?keyfrom=null',
            'product' => 'DICT',
            'type' => 1,
            'um' => 'true',
            'username' => $this->username,
            'password' => md5($this->password),
            'agreePrRule' => 1,
            'savelogin' => 1,
        ];
    }


    /**
     * Description:
     * Read data from a remote file/url, essentially a shortcut for curl
     *
     * @param $url  - URL to request
     * @param $options  - Array of curl options
     * @return mixed
     */
    public function request($url = null, $options = null)
    {
        if (is_null($url)):
            return false;
        endif;

        $defaults = array(                                    // Create a list of default curl options
            CURLOPT_RETURNTRANSFER => true,                    // Returns the result as a string
            CURLOPT_URL => $url,                            // Sets the url to request
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => realpath(self::COOKIE_FILE), // 保存返回的Cookie
            CURLOPT_COOKIEFILE => realpath(self::COOKIE_FILE), // 读取现有Cookie, 不需要自己维护cookie变量
            CURLOPT_TIMEOUT => 30                          // 增加超时
        );

        if ($options):
            foreach ($options as $k => $v):
                $defaults[$k] = $v;
            endforeach;
        endif;

        array_filter($defaults, array($this, 'empty_filter'));  // Filter out empty options from the array

        $ch = curl_init();                                    // Init new curl object
        curl_setopt_array($ch, $defaults);                // Set curl options
        $out = curl_exec($ch);                            // Request remote data
        $err = curl_error($ch);
        curl_close($ch);                                    // End curl request

        if ($err):
            return $err;
        else:
            return $out;
        endif;
    }

    /**
     * Description:
     * Remove all items from an associative array that do not have a value
     *
     * @param $a  - Associative array
     * @return bool
     */
    private function empty_filter($a)
    {
        if ($a == '' || $a == null):                        // if $a is empty or null
            return false;                                    // return false, else, return true
        else:
            return true;
        endif;
    }
}