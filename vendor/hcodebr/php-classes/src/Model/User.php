<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login, $password)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN",
			[":LOGIN"=>$login]
		);

		if(count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida. ");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();
			
			return $user;

		}
		else
		{
			throw new \Exception("Usuário inexistente ou senha inválida. ");
		}
	}

	public static function verifyLogin($inAdmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION]) 
			||
			!$_SESSION[User::SESSION] 
			||
			!(int)$_SESSION[User::SESSION]['iduser'] > 0 
			|| 
			(bool)$_SESSION[User::SESSION]['inadmin'] !== $inAdmin
		){
			header("Location: /admin/login");

			exit;
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
		header("Location: /admin/login");
		exit;
	}

	public static function listAll()
	{
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_users a  INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");


	}

	public function save(){

		$sql = new Sql();
		try {
			$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword,:desemail, :nrphone, :inadmin)",
				array(
					":desperson"=>$this->getdesperson(),
					":deslogin"=>$this->getdeslogin(),
					":despassword"=>$this->getdespassword(),
					":desemail"=>$this->getdesemail(),
					":nrphone"=>$this->getnrphone(),
					":inadmin"=>$this->getinadmin()
				));
			$this->setData($results[0]);
		} catch (Exception $e) {
			throw new \Exception($e->getMessage());	
		}
	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a inner join tb_persons b USING(idperson) where a.iduser = :iduser", array(":iduser"=>$iduser));

		$this->setData($results[0]);
	}

	public function update()
	{

		$sql = new Sql();
		try {
			$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson, :deslogin, :despassword,:desemail, :nrphone, :inadmin)",
				array(
					":iduser"=>$this->getiduser(),
					":desperson"=>$this->getdesperson(),
					":deslogin"=>$this->getdeslogin(),
					":despassword"=>$this->getdespassword(),
					":desemail"=>$this->getdesemail(),
					":nrphone"=>$this->getnrphone(),
					":inadmin"=>$this->getinadmin()
				));

			$this->setData($results[0]);

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());	
		}
	}

	public function delete()
	{
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)", array(":iduser"=>$this->getiduser()));
	}

	public static function getForgotten($email)
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT * FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :EMAIL
			",[
				":EMAIL"=>$email
			]
		);

		if(count($results === 0))
		{
			throw new \Exception("Não possível recuperar a senha, cuzão", 1);
		}
		else
		{
			$data = $results[0];

			$resultsRecovery = $sql->select("CALL sp_userpasswordsrecoveries_create(:iduser, :desip)",
				[
					":iduser"=>$data['iduser'],
					":desip"=>$_SERVER['REMOTE_ADDR']
				]
			);
			if(count($resultsRecovery === 0))
			{
				throw new \Exception("Não possível recuperar a senha, cuzão", 1);
			}
			else
			{
				$dataRecovery = $resultsRecovery[0];

				base64_encode(hash_hmac('sha256',$dataRecovery));
			}


		}
	}
}