<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model{

	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	public function save(){

		$sql = new Sql();
		try {
			$results = $sql->select("CALL sp_categories_save(:idcategory,:descategory)",
				array(
					":idcategory"=>$this->getidcategory(),
					":descategory"=>$this->getdescategory()
				));
			$this->setData($results[0]);

			Category::updateFile();

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());	
		}
	}

	public function get($idcategory)
	{
		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",
			array(
				":idcategory" => $idcategory
			)
		);

		$this->setData($result[0]);
	}

	public function delete(){
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory =:idcategory",
			array(
				":idcategory"=>$this->getidcategory()
			)
		);

		Category::updateFile();
	}

	public static function updateFile()
	{
		$categories  = Category::listAll();

		$html = [];

		foreach ($categories as $value) {
			array_push($html,'<li><a href="/category/'.$value['idcategory'].'">'.$value['descategory'].'</a></li>');
			file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."view".DIRECTORY_SEPARATOR."categories-menu.html",implode('',$html));
		}

	}

}