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

    public function __construct($keys)
    {
        $this->workflow = new Workflow;
        $this->keys = $keys;
        $this->historyFile = 'YoudaoTranslate-'. @date('Ym') .'.log';
    }

    /**
     * @param 要翻译的值
     * @return 翻译结果， json
     */
    public function translate($query)
    {
        $this->query = $query;
        // 如果输入的是 yd * ，列出查询记录最近10条
        if ($this->query === '*'){
            return $this->getHistory();
        }

        $url = $this->getOpenQueryUrl($query);

        $response = $this->workflow->request($url);
        $this->result   = json_decode($response);

        if( empty($this->result) || (int)$this->result->errorCode !== 0){
            //证明翻译出错
            $this->addItem('翻译出错', $response, $response);
        }else{
            // 获取要发音的单词
            $this->getPronounce();

            if(isset($this->result->translation)){
                $this->parseTranslation($this->result->translation);
            }

            if(isset($this->result->basic)){
                $this->parseBasic($this->result->basic);
            }

            if(isset($this->result->web)){
                $this->parseWeb($this->result->web);
            }
        }

        return $this->workflow->output();
    }

    /**
     * 解析 Translation 字段， 释义
     * @param Translation Object
     * @return array
     */
    private function parseTranslation($translation)
    {
        $this->addItem($translation[0], null);
    }

    /**
     * 解析 Basic 字段， 基础释义
     * @param Basic Object
     * @return array
     */
    private function parseBasic($basic)
    {
        foreach ($basic->explains as $explain) {
            $this->addItem($explain, null);
        }

        if(isset($basic->phonetic)){
            // 获取音标，同时确定要发音的单词
            $phonetic = $this->getPhonetic($basic);
            $this->addItem($phonetic, '回车可听发音', '~'.$this->pronounce);
        }
    }

    /**
     * 解析 Web 字段， 网络释义
     * @param Web Object
     * @return array
     */
    private function parseWeb($web)
    {
        foreach ($web as $key => $item) {
            $_title = join(',', $item->value);
            if ($key === 0) {
                $result = $this->addItem($_title, $item->key, $key, true);
                $this->saveHistory($result);
            } else {
                $this->addItem($_title, $item->key, $_title);
            }
        }
    }

    /**
     *function：检测字符串是否由纯英文，纯中文，中英文混合组成
     *param string
     *return 1:纯英文;2:纯中文;3:中英文混合
     */
    private function isChinese($str){
        $m=mb_strlen($str,'utf-8');
        $s=strlen($str);
        if($s==$m){
            return false;
        }
        if($s%$m==0&&$s%3==0){
            return true;
        }
        return true;
    }

    /**
     * 从 basic 字段中获取音标
     * @param Basic Object
     * @return array
     */
    public function getPhonetic($basic)
    {
        $phonetic = '';
        // 中文才会用到这个音标y
        if ($this->isChinese($this->query) && isset($basic->{'phonetic'}))
            $phonetic .= "[".$basic->{'phonetic'}."]";
        if (isset($basic->{'us-phonetic'}))
            $phonetic .= " [美: ".$basic->{'us-phonetic'}."]";
        if (isset($basic->{'uk-phonetic'}))
            $phonetic .= " [英: ".$basic->{'uk-phonetic'}."]";

        return $phonetic;
    }

    /**
     * 获取要发音的单词
     */
    public function getPronounce()
    {
        if($this->isChinese($this->query)){
            $this->pronounce = $this->result->translation[0];
        }else{
            $this->pronounce = $this->query;
        }
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
    * @param translation
    * @return array
    */
    private function saveHistory($translation)
    {
        @file_put_contents($this->historyFile, json_encode($translation) . "\n", FILE_APPEND);
    }

    /**
     * 取文件最后$n行
     * @param string $file 文件路径
     * @param int $line 最后几行
     * @return mixed 成功则返回字符串
     */
    private function getLastLines($filename,$n)
    {
        if (!$handler = @fopen($filename, 'r')) {
            return false;
        }

        $eof = "";
        $lines = [];
        //忽略最后的 \n
        $position = -2;

        while($n>0){
            while($eof!="\n"){
                if(!fseek($handler, $position, SEEK_END)){
                    $eof = fgetc($handler);
                    $position--;
                } else {
                    break;
                }
            }

            if ($line = fgets($handler)) {
                $lines[] = $line;
                $eof="";
                $n--;
            } else {
                //当游标超限 fseek 报错以后，无法 fgets($fp), 需要将游标向后移动一位
                fseek($handler, $position+1, SEEK_END);
                if ($line = fgets($handler)) {
                    $lines[] = $line;
                }
                break;
            }

        }
        return $lines;
    }

    /**
     * 随机从配置中获取一组 keyfrom 和 key
     * @param $title 标题
     * @param $subtitle 副标题
     * @param $arg 传递值
     * @return array
     */
    private function addItem($title, $subtitle, $arg = null, $toArray = false)
    {
        $arg           = $arg ? $arg : $title;
        $_subtitle     = $subtitle ? $subtitle : $this->query;
        $_quicklookurl = 'http://youdao.com/w/'.urlencode($this->query);
        $_icon         = $this->startsWith($arg, '~') ? 'translate-say.png' : 'translate.png';

        $result = $this->workflow->result()
                    ->title($title)
                    ->subtitle($_subtitle)
                    ->quicklookurl($_quicklookurl)
                    ->arg($arg)
                    ->icon($_icon)
                    ->text('copy', $title);
        
        if ($toArray) {
            return $result->toArray();
        }
    }


    /**
     * 检测字符串开头
     * @param haystack 等待检测的字符串
     * @param needle   开头的定义
     * @return Boolean
     */
    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * 组装网易智云请求地址
     * @return String
     */
    private function getOpenQueryUrl($query)
    {
        $api = 'https://openapi.youdao.com/api?from=auto&to=auto&';

        $key = $this->keys[array_rand($this->keys)];
        $key['q'] = $query;
        $key['salt'] = strval(rand(1,100000));
        $key['sign'] = md5($key['appKey'] . $key['q'] . $key['salt'] . $key['secret']);

        return $api.http_build_query($key);
    }
}
