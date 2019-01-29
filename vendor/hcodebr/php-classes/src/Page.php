<?php 

namespace Hcode;

use Rain\Tpl;

class Page
{
	private $tpl;
	private $options = [];
	private $defaults = [
		"data"=>[]
	];

	private function setData($data = []){
		foreach ($data as $key => $value) {
			$this->tpl->assign($key,$value);
		}
	}


	public function __construct($options = [])
	{
		$this->options = array_merge($this->defaults,$options);

		$config = array(
			"tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/view/",
			"cache_dir"     => $_SERVER['DOCUMENT_ROOT']."view/cache/",
			"debug"         => false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		$this->tpl->draw("header");

	}

	public function setTpl($templateName,$data = [], $returnHTML = false)
	{
		$this->setData($data);

		return $this->tpl->draw($templateName,$returnHTML);
	}

	public function __destruct()
	{
		$this->tpl->draw("footer");
	}
}

?>