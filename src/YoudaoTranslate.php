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

    public function __construct($keys)
    {
        $this->workflow = new Workflow;
        $this->keys = $keys;
    }

    /**
     * @param 要翻译的值
     * @return 翻译结果， json
     */
    public function translate($query)
    {
        $this->query = $query;
        $url = $this->getOpenQueryUrl($query);

        $response = $this->workflow->request($url);
        $this->result   = json_decode($response);

        if( empty($this->result) || (int)$this->result->errorCode !== 0){
            //证明翻译出错
            $this->addItem('翻译出错', $response, $response);
        }else{
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
        $this->addItem($translation[0], null, $translation[0]);
    }

    /**
     * 解析 Basic 字段， 基础释义
     * @param Basic Object
     * @return array
     */
    private function parseBasic($basic)
    {
        foreach ($basic->explains as $explain) {
            $this->addItem($explain, null, $explain);
        }

        if(isset($basic->phonetic)){
            $this->getPronounce($basic);
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
     * 从 basic 字段中获取发音
     * @param Basic Object
     * @return array
     */
    public function getPronounce($basic)
    {
        $phonetic = '';
        if (isset($basic->{'phonetic'}))
            $phonetic .= "[".$basic->{'phonetic'}."]";
        if (isset($basic->{'us-phonetic'}))
            $phonetic .= " [美: ".$basic->{'us-phonetic'}."]";
        if (isset($basic->{'uk-phonetic'}))
            $phonetic .= " [英: ".$basic->{'uk-phonetic'}."]";
        
        if($this->isChinese($this->query)){
             $pronounce = $this->query.'  '.$this->result->translation[0];
         }else{
             $pronounce = $this->query;
         }
        $this->addItem($phonetic, '回车可听发音', '~'.$pronounce);
    }

    /**
     * 解析 Web 字段， 网络释义
     * @param Web Object
     * @return array
     */
    private function parseWeb($web)
    {
        foreach ($web as $item) {
            $_title = join(',', $item->value);
            $this->addItem($_title, $item->key, $_title);
        }
    }

    /**
     * 随机从配置中获取一组 keyfrom 和 key
     * @param $title 标题
     * @param $subtitle 副标题
     * @param $arg 传递值
     * @return array
     */
    private function addItem($title, $subtitle, $arg)
    {
        $_subtitle     = $subtitle ? $subtitle : $this->query;
        $_quicklookurl = 'http://youdao.com/w/'.urlencode($this->query);
        $_icon         = $this->startsWith($arg, '~') ? 'translate-say.png' : 'translate.png';

        $this->workflow->result()
                 ->title($title)
                 ->subtitle($_subtitle)
                 ->quicklookurl($_quicklookurl)
                 ->arg($arg)
                 ->icon($_icon)
                 ->text('copy', $title);
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