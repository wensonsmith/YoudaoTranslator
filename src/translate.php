<?php

require_once('workflows.php');

class Translate{

	private $url = "http://fanyi.youdao.com/openapi.do?keyfrom=SeekBetterMe&key=164530784&type=data&doctype=json&version=1.1&q=";

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