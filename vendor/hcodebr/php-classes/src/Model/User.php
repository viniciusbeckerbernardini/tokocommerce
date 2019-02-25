<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class User extends Model{

	const SESSION = "User";
	const SECRET = "php7openssl";

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
		$query = $sql->select("
			SELECT * FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :EMAIL
			",[
				":EMAIL"=>$email
			]
		);

		$results = $query;

		// print_r($results);

		if(empty($results))
		{
			throw new \Exception("Não possível recuperar a senha, cuzão", 1);
		}
		else
		{
			$data = $results[0];

			$recoveryQuery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER['REMOTE_ADDR']
			));

			$resultsRecovery = $recoveryQuery;

			// var_dump($resultsRecovery);

			if(empty($resultsRecovery))
			{
				throw new \Exception("Não possível recuperar a senha, cuzão", 1);
			}
			else
			{
				$dataRecovery = $resultsRecovery[0];
				$cipher = 'aes-128-gcm';
				if(in_array($cipher, openssl_get_cipher_methods()))
				{
					$ivlen = openssl_cipher_iv_length($cipher);
					$iv = openssl_random_pseudo_bytes($ivlen);
					$encyptedData = openssl_encrypt($dataRecovery['idrecovery'], $cipher,User::SECRET,$options = 0, $iv, $tag);

					$link = 'http://ecommerce.local.com/admin/forgot/reset?code=$encyptedData';

					$mailer = new Mailer($data['desemail'],$data['desperson'],"Recuperação de senha", "forgot", ["name"=>$data['desperson'],"link"=>$link]);

					$mailer->send();

					return $data;
				}

			}


		}
	}
}