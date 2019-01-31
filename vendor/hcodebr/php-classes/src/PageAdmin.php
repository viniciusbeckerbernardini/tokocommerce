<?php 


namespace Hcode; 

use Rain\Tpl;

class PageAdmin extends Page{
	
	public function __construct($opts = [], $tpl_dir = '/view/admin/')
	{

		parent::__construct($opts,$tpl_dir);

	}
}