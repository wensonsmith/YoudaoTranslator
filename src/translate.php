<?php

require_once('workflows.php');

class Translate{

	private $url = "http://fanyi.youdao.com/openapi.do?type=data&doctype=json&version=1.1&";

	function __construct(){
		$pair = $this->getRandomKey();
		$this->url .= "keyfrom=".$pair['keyfrom']."&key=".$pair['key']."&q=";
	}

	private function getRandomKey()
	{
		$keys  = array(
			array('key' => '541488500', 'keyfrom'=>'ScentRemains'),
			array('key' => '164530784', 'keyfrom'=>'SeekBetterMe'),
			array('key' => '1813511369', 'keyfrom'=>'Bro2Win'),
			array('key' => '2046568483', 'keyfrom'=>'SanXiShi')
		);

		return $keys[array_rand($keys)];
	}

	public function getTranslation($query){
		$workflows = new Workflows();
		$api = $this->url.urlencode($query);
		$res = $workflows->request($api);
		$res = json_decode( $res );
		if ($res->errorCode == 0) {
			$workflows->result(     $query,
									$res->translation[0],
									$res->translation[0],
									$query,
									"translate.png");

			if(isset($res->basic)){
				$phonetic = "";
				if (isset($res->basic->{'phonetic'}))
					$phonetic .= "[".$res->basic->{'phonetic'}."]";
				if (isset($res->basic->{'us-phonetic'}))
					$phonetic .= " [美: ".$res->basic->{'us-phonetic'}."]";
				if (isset($res->basic->{'uk-phonetic'}))
					$phonetic .= " [英: ".$res->basic->{'uk-phonetic'}."]";

				if (!empty($phonetic))
					$workflows->result('', $phonetic, $phonetic, $query, "translate.png");

				$explains = $res->basic->explains;

				foreach ($explains as $key => $value) {
					$workflows->result($key,
									$value,
									$value,
									$query,
									"translate.png");
				}
			}

			if (isset($res->web)) {
				$web = $res->web;
				foreach ($web as $key => $item) {
					$workflows->result($key,
									implode(",", $item->value),
									implode(",", $item->value),
									$item->key,
									"translate.png");
				}
			}
			
		}else{
			$workflows->result(	'',
		  						'',
					  			'没查到呀', 
					  			'没找到对应的翻译',
					  			'translate.png',false);
		}

		echo $workflows->toxml();
	}

}