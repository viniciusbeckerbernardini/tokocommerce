<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model{

	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}

	public static function checkList($list){
		foreach ($list as &$row) {
			$value = new Product();
			$value->setData($row);
			$row = $value->getValues();
		}

		return $list;
	}

	public function save()
	{
		$sql = new Sql();
		try {
			$results = $sql->select("CALL sp_products_save(:idproduct,:desproduct,:vlprice,:vlwidth,:vlheight,:vllength,:vlweight,:desurl)",
				array(
					":idproduct"=>$this->getidproduct(),
					":desproduct"=>$this->getdesproduct(),
					":vlprice"=>$this->getvlprice(),
					":vlwidth"=>$this->getvlwidth(),
					":vlheight"=>$this->getvlheight(),
					":vllength"=>$this->getvllength(),
					":vlweight"=>$this->getvlweight(),
					":desurl"=>$this->getdesurl()
				));
			$this->setData($results[0]);

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());	
		}
	}

	public function get($idproduct)
	{
		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",
			array(
				":idproduct" => $idproduct
			)
		);

		$this->setData($result[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct =:idproduct",
			array(
				":idproduct"=>$this->getidproduct()
			)
		);
	}

	public function checkPhoto()
	{
		$path = $_SERVER['DOCUMENT_ROOT'].
		DIRECTORY_SEPARATOR.
		'res'.
		DIRECTORY_SEPARATOR.
		'site'.
		DIRECTORY_SEPARATOR.
		'img'.
		DIRECTORY_SEPARATOR.
		'products'.
		DIRECTORY_SEPARATOR.
		$this->getidproduct().'.jpg';
		if(file_exists($path)){
			return $this->setdesphoto("/res/site/img/products/".$this->getidproduct().".jpg");
		}else{
			return $this->setdesphoto("/res/site/img/product.jpg");
		}
	}

	public function getValues(){
		$this->checkPhoto();
		$values = parent::getValues(); 	
		return $values;
	}

	public function setPhoto($file)
	{
		$extension = explode('.',$file["name"]);
		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;
			case 'gif':
			$image = imagecreatefromgif($file["tmp_name"]);
			break;
			case 'png':
			$image = imagecreatefrompng($file["tmp_name"]);	
			break;
			default:
				# code...
			break;
		}

		$path = $_SERVER['DOCUMENT_ROOT'].
		DIRECTORY_SEPARATOR.
		'res'.
		DIRECTORY_SEPARATOR.
		'site'.
		DIRECTORY_SEPARATOR.
		'img'.
		DIRECTORY_SEPARATOR.
		'products'.
		DIRECTORY_SEPARATOR.
		$this->getidproduct().'.jpg';
		
		imagejpeg($image,$path);

		imagedestroy($image);

		$this->checkPhoto();
	}

	public function getFromURL($desurl)
	{
		$sql = new Sql();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl;",
			[
				':desurl'=>$desurl
			]
		);
		$this->setData($rows[0]);
	}

	public function getCategories(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories a 
			INNER JOIN tb_productscategories b 
			ON a.idcategory = b.idcategory 
			WHERE b.idproduct = :idproduct",
			[':idproduct'=> $this->getidproduct()]
		);
	}

    public static function getPage(int $page = 1, int $itemsPerPage = 10){

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_products ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
			");

        $resultsTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>$results,
            'total'=>$resultsTotal[0]["nrtotal"],
            'pages'=>ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

    public static function getPageSearch(string $search, int $page = 1, int $itemsPerPage = 10){

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE desproduct LIKE :search
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage
			",
            [
                ":search"=>"%".$search."%"
            ]);

        $resultsTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>$results,
            'total'=>$resultsTotal[0]["nrtotal"],
            'pages'=>ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }
}