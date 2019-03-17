<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";
	const SECRET = "php7openssl_2019_";

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

			if(empty($resultsRecovery))
			{
				throw new \Exception("Não possível recuperar a senha, cuzão", 1);
			}
			else
			{
				$dataRecovery = $resultsRecovery[0];
				$cipher = 'aes-128-ecb';
				if(in_array($cipher, openssl_get_cipher_methods()))
				{
					$ivlen = openssl_cipher_iv_length($cipher);
					$iv = openssl_random_pseudo_bytes($ivlen);
					$encyptedData = base64_encode(openssl_encrypt($dataRecovery['idrecovery'], $cipher,User::SECRET,$options = 0, $iv));

					$link = "http://ecommerce.local.com/admin/forgot/reset?code=$encyptedData";

					$mailer = new Mailer($data['desemail'],$data['desperson'],"Recuperação de senha", "forgot", ["name"=>$data['desperson'],"link"=>$link]);

					$mailer->send();

					return $data;
				}
			}
		}
	}

	public static function validForgotDecrypt($code){
		
		$cipher = 'aes-128-ecb';
		if(in_array($cipher, openssl_get_cipher_methods()))
		{
			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = openssl_random_pseudo_bytes($ivlen);
			$idrecovery = openssl_decrypt(base64_decode($code), $cipher, User::SECRET,$options = 0,$iv);
		}

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b using(iduser)
			INNER JOIN tb_persons c using(idperson)
			where 
			a.idrecovery = :idrecovery
			and 
			a.dtrecovery is null
			and
			date_add(a.dtregister, interval 1 hour) >= NOW();
			",
			[":idrecovery" => $idrecovery
		]
	);
		if(count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha.");
		}else{
			return $results[0];
		}
	}

	public static function setForgotUsed($idrecovery){
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries set dtrecovery = NOW() where idrecovery = :idrecovery",
			[
				":idrecovery"=>$idrecovery
			]);
	}

	public function setPassword($password){
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",
			[
				":password"=>$password,
				":iduser"=>$this->getiduser()
			]
		);
	}
}