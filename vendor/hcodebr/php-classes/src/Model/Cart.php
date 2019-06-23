<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;


class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	public static function getFromSession()
	{
		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 )
		{
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		} else	{
			$cart->getFromSessionID();

			if(!(int)$cart->getidcart() > 0 ){
				$data = [
					'dessessionid'=>session_id()
				];

				if(User::checkLogin(false)){
					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();
				}
				
				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}
		}

		return $cart;
	}

	public function setToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}



	public function getFromSessionID()
	{
		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",
			array(
				":dessessionid" => session_id()
			)
		);
		if(count($result) > 0 ){
			$this->setData($result[0]);
			
		}
	}



	public function get(int $idcart)
	{
		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",
			array(
				":idcart" => $idcart
			)
		);
		if(count($result) > 0 ){
			$this->setData($result[0]);
		}
	}

	public function save()
	{
		$sql = new Sql();
		$result = $sql->select("CALL sp_carts_save(:idcart,:dessessionid,:iduser,:deszipcode,:vlfreight,:nrdays)",
			[
				':idcart'=>$this->getidcart(),
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()
			]);

		$this->setData($result[0]);
	}

	public function addProduct(Product $product)
	{		
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",
			[
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]
		);

		$this->getCalculateTotal();
	}

	public function removeProduct(Product $product, bool $all = false)
	{
		$sql = new Sql();

		if($all){
			$sql->query("UPDATE tb_cartsproducts set dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct and dtremoved IS NULL",
				[
					':idcart'=>$this->getidcart(),
					'idproduct'=>$product->getidproduct()
				]
			);
		}else{
			$sql->query("UPDATE tb_cartsproducts set dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",
				[
					':idcart'=>$this->getidcart(),
					'idproduct'=>$product->getidproduct()
				]
			);
		}

		$this->getCalculateTotal();
	}

	public function getProducts()
	{
		$sql = new Sql();

		return Product::checkList($sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) as nrqtd, SUM(b.vlprice) as vltotal from tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY  b.desproduct
			",
			[
				':idcart'=>$this->getidcart()
			]));
	}

	public function getProductsTotals()
	{

		$sql = new Sql();

		$result = $sql->select("
			SELECT SUM(vlprice) as vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) as vlheigth, SUM(vllength) as vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL
			", 
			[
				':idcart'=>$this->getidcart()
			]);

		if(count($result) > 0){
			return $result[0];
		}else{
			return [];
		}
	}

	public function setFreight(string $nrzipcode){

		$nrzipcode = str_replace('-','', $nrzipcode);

		$totals = $this->getProductsTotals();


		if($totals['nrqtd'] > 0){

			$infoForQuery = [
				'nCdEmpresa' => '',
				'sDsSenha' => '',
				'nCdServico' => '04510',
				'sCepOrigem' => '91140400',
				'sCepDestino' => $nrzipcode,
				'nVlPeso' => $totals['vlweight'] / 1000,
				'nCdFormato' => 1,
				'nVlComprimento' => $totals['vllength'],
				'nVlAltura' => $totals['vlheigth'],
				'nVlLargura' => $totals['vlwidth'],
				'nVlDiametro' => 0,
				'sCdMaoPropria' => 'S',
				'nVlValorDeclarado' => $totals['vlprice'],
				'sCdAvisoRecebimento' => 'S'
			];

			$qs = http_build_query($infoForQuery);

			$xml = simplexml_load_file('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?'.$qs);

			$result = $xml->Servicos->cServico;

			if($result->MsgErro != ''){
				Cart::setMsgError($result->MsgErro);
				return;
			}else{
				Cart::clearMsgError();
			}


			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(number_format((float)$result->Valor,2,'.',''));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;
		}else{

		}
	}

	public static function setMsgError(string $msg){
		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}

	public static function getMsgError(){
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : '';

		Cart::clearMsgError();

		return $msg;
	}

	public static function clearMsgError(){
		unset($_SESSION[Cart::SESSION_ERROR]);
	}

	public function updateFreight(){
		if($this->getdeszipcode() != ''){
			$this->setFreight($this->getdeszipcode());
		}
	}

	public function getValues(){
		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal(){
		
		$this->updateFreight();
		$totals = $this->getProductsTotals();
		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());



	}

}