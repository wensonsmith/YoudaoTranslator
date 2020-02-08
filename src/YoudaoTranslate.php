<?php

require 'vendor/autoload.php';

use Alfred\Workflows\Workflow;

// $workflow->result()
//          ->uid('bob-belcher')   唯一编号 : STRING (可选)，用于排序
//          ->title('Bob')         标题： STRING， 显示结果
//          ->subtitle('Head Burger Chef')  副标题： STRING ,显示额外的信息
//          ->quicklookurl('http://www.bobsburgers.com')  快速预览地址 : STRING (optional)
//          ->type('default')   类型，可选择文件类型: "default" | "file"
//          ->arg('bob')    输出参数 : STRING (recommended)，传递值到下一个模块
//          ->valid(true)       回车是否可用 : true | false (optional, default = true)
//          ->icon('bob.png')   图标
//          ->mod('cmd', 'Search for Bob', 'search')   修饰键 : OBJECT (可选)
//          ->text('copy', 'Bob is the best!')   按cmd+c 复制出来的文本: OBJECT (optional)
//          ->autocomplete('Bob Belcher');    自动补全 : STRING (recommended)

class YoudaoTranslate
{
    private $workflow;
    private $keys;
    private $result;
    private $query;
    private $pronounce;
    private $historyFile;

    /**
     * @var boolean $queryChinese 减少多次调用 isChinese 方法
     */
    private $queryChinese;

    private $phonetic;

    public function __construct($keys)
    {
        $this->workflow = new Workflow;
        $this->keys = $keys;
        $this->historyFile = 'YoudaoTranslate-'.@date('Ym').'.log';
    }

    /**
     * @param  string  $query  要翻译的值
     * @return mixed 翻译结果
     */
    public function translate($query)
    {
        /**
         * @see https://www.php.net/manual/en/function.iconv.php#111909
         * @see https://stackoverflow.com/questions/48537305/same-character-different-length-and-bytes
         */
        $query = iconv("UTF-8-MAC", "UTF-8", $query);

        $this->query = $this->isCamelCase($query) ? $this->parseCamelPhrase($query) : $query;

        $this->queryChinese = $this->isChinese($query);

        // 如果输入的是 yd * ，列出查询记录最近10条
        if ($this->query === '*') {
            return $this->getHistory();
        }

        $url = $this->getOpenQueryUrl($this->query);

        $response = $this->workflow->request($url);
        $this->result = json_decode($response);

        if (empty($this->result) || (int) $this->result->errorCode !== 0) {
            // 证明翻译出错
            $error = $this->parseError($this->result->errorCode);
            $this->addItem('翻译出错', $error);
        } else {

            // 为了加入生词本有发音，优先解析发音
            if (isset($this->result->basic)) {
                $this->getPhonetic($this->result->basic);
            }

            if (isset($this->result->translation)) {
                $this->parseTranslation($this->result->translation);
            }

            if (isset($this->result->basic)) {
                $this->parseBasic($this->result->basic);
            }

            if (isset($this->result->web)) {
                $this->parseWeb($this->result->web);
            }
        }

        return $this->workflow->output();
    }

    /**
     * 解析 Translation 字段， 释义
     * @param object $translation
     */
    private function parseTranslation($translation)
    {
        $this->pronounce = $this->queryChinese ? $translation[0] : $this->query;
        $this->addItem($translation[0], $this->query);
    }

    /**
     * 解析 Basic 字段， 基础释义
     * @param object $basic
     */
    private function parseBasic($basic)
    {
        foreach ($basic->explains as $explain) {
            $this->pronounce = $this->queryChinese ? $explain : $this->query;
            $this->addItem($explain, $this->query);
        }

        if (isset($basic->phonetic)) {
            // 获取音标，同时确定要发音的单词

            $this->addItem($this->phonetic, '回车可听发音', '~'.$this->pronounce);
        }
    }

    /**
     * 解析 Web 字段， 网络释义
     * @param object $web
     */
    private function parseWeb($web)
    {

        foreach ($web as $index => $item) {
            $this->pronounce = $this->queryChinese ? $item->value[0] : $item->key;
            $title = join(', ', $item->value);

            if ($index === 0) {
                $result = $this->addItem($title, $item->key, $item->value[0], true);
                $this->saveHistory($result);
            } else {
                $this->addItem($title, $item->key, $item->value[0]);
            }
        }
    }

    /**
     * 返回有道云部分错误
     * @param  int  $code
     * @return mixed
     */
    private function parseError($code)
    {
        $messages = [
            101 => '缺少必填的参数',
            102 => '不支持的语言类型',
            103 => '翻译文本过长',
            108 => '应用ID无效',
            110 => '无相关服务的有效实例',
            111 => '开发者账号无效',
            112 => '请求服务无效',
            113 => '查询为空',
            202 => '签名检验失败,检查 KEY 和 SCRET',
            401 => '账户已经欠费',
            411 => '访问频率受限'
        ];

        return isset($messages[$code]) ? $messages[$code] : '翻译失败，错误码：' . $code;
    }

    /**
     * 检查是否为中文
     * @param string $str
     * @return false|int
     */
    private function isChinese($str)
    {
        $chinese = '/[\x{4e00}-\x{9fa5}]+/u';
        return preg_match($chinese, $str);
    }

    /**
     * 检查是不是 CamelCase
     * @param string $str
     * @return false|int
     */
    private function isCamelCase($str)
    {
        $regex = '/([A-Z]|[a-z])+((\d)|([A-Z0-9][a-z0-9]+))*([A-Z])[a-z]*/';
        return preg_match($regex, $str);
    }

    /**
     * 解析camel 短语
     * @param string $input
     * @return string
     */
    private function parseCamelPhrase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode(' ', $ret);
    }

    /**
     * 从 basic 字段中获取音标
     * @param object $basic
     * @return mixed
     */
    public function getPhonetic($basic)
    {
        $phonetic = '';
        // 中文才会用到这个音标y
        if ($this->queryChinese && isset($basic->{'phonetic'})) {
            $phonetic .= "[".$basic->{'phonetic'}."] ";
        }
        if (isset($basic->{'us-phonetic'})) {
            $phonetic .= " [美: ".$basic->{'us-phonetic'}."] ";
        }
        if (isset($basic->{'uk-phonetic'})) {
            $phonetic .= " [英: ".$basic->{'uk-phonetic'}."]";
        }

        $this->phonetic = $phonetic;

        return $phonetic;
    }

    /**
     * 获取查询记录的最近 9 条
     */
    private function getHistory()
    {
        $history = [];
        $lastTenLines = $this->getLastLines($this->historyFile, 9);
        if (!empty($lastTenLines)) {
            foreach ($lastTenLines as $line) {
                $result = json_decode($line);
                if (strlen($result->subtitle) > 1) {
                    $history[] = $result;
                }
            }

            $output = [
                'items' => $history
            ];

            return json_encode($output);
        } else {
            $this->addItem('没有历史纪录', 'No History');
            return $this->workflow->output();
        }
    }

    /**
     * 保存翻译结果
     * @param  array $translation
     */
    private function saveHistory($translation)
    {
        @file_put_contents($this->historyFile, json_encode($translation)."\n", FILE_APPEND);
    }

    /**
     * 取文件最后$n行
     * @param  string  $filename  文件路径
     * @param  int  $n  最后几行
     * @return mixed 成功则返回字符串
     */
    private function getLastLines($filename, $n)
    {
        if (!$handler = @fopen($filename, 'r')) {
            return false;
        }

        $eof = "";
        $lines = [];
        //忽略最后的 \n
        $position = -2;

        while ($n > 0) {
            while ($eof != "\n") {
                if (!fseek($handler, $position, SEEK_END)) {
                    $eof = fgetc($handler);
                    $position--;
                } else {
                    break;
                }
            }

            if ($line = fgets($handler)) {
                $lines[] = $line;
                $eof = "";
                $n--;
            } else {
                //当游标超限 fseek 报错以后，无法 fgets($fp), 需要将游标向后移动一位
                fseek($handler, $position + 1, SEEK_END);
                if ($line = fgets($handler)) {
                    $lines[] = $line;
                }
                break;
            }

        }
        return $lines;
    }

    /**
     * 添加一个选项
     * @param  string  $title  标题
     * @param  string  $subtitle  副标题
     * @param  string  $arg  传递值
     * @param  boolean  $returnValue  为了保存历史记录，需要返回数组
     * @return array
     */
    private function addItem($title, $subtitle, $arg = null, $returnValue = false)
    {
        $arg = $arg ? $arg : $title;
        $quickLookUrl = 'http://youdao.com/w/' . rawurlencode($this->query);
        $icon = $this->startsWith($arg, '~') ? 'translate-say.png' : 'translate.png';

        $result = $this->workflow->result()
            ->title($title)
            ->subtitle($subtitle)
            ->quicklookurl($quickLookUrl)
            ->arg($arg)
            ->mod('cmd', '🔊' . $this->pronounce, $this->pronounce)
            ->mod('alt', '🔊' . $this->pronounce, $this->pronounce)
            ->mod('ctrl', '📝 加入生词本', implode('|', [$this->pronounce, $this->phonetic, $title]))
            ->icon($icon)
            ->text('copy', $title);

        if ($returnValue) {
            return $result->toArray();
        }
    }


    /**
     * 检测字符串开头
     * @param string $haystack 等待检测的字符串
     * @param string $needle   开头的定义
     * @return boolean
     */
    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * 组装网易智云请求地址
     * @see https://ai.youdao.com/DOCSIRMA/html/自然语言翻译/API文档/文本翻译服务/文本翻译服务-API文档.html
     * @param string $query
     * @return string
     */
    private function getOpenQueryUrl($query)
    {

        $api = 'https://openapi.youdao.com/api?';

        $key = $this->keys[array_rand($this->keys)];
        $key['q'] = $query;
        $key['salt'] = strval(rand(1, 100000));
        $key['sign'] = md5($key['appKey'].$key['q'].$key['salt'].$key['secret']);

        // 有道新版 api 只有当 from 和 to 的值都在{zh-CHS, en}范围内时，
        // 才有单词字典翻译信息，当两个都是 auto 时则没有
        if ($this->queryChinese) {
            $key['from'] = 'zh-CHS';
            $key['to'] = 'en';
        } else {
            $key['from'] = 'auto';
            $key['to'] = 'zh-CHS';
        }

        unset($key['secret']);

        return $api.http_build_query($key);
    }
}
