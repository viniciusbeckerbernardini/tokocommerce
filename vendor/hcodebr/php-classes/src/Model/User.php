<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";
	const SECRET = "php7openssl_2019_";
	const SESSION_USER = "UserError";
	const SESSION_ERROR_USER = "UserErrorRegister";
	const SESSION_MESSAGE_USER = "UserMessageRegister";

	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
			
			$user->setData($_SESSION[User::SESSION]);

		}
		return $user;

	}

	public function setToSession(){
        $_SESSION[User::SESSION] = $this->getValues();
    }


	public static function checkLogin($inAdmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]['iduser'] > 0 
		){
			return false;
		}else{
			if($inAdmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){;
				return true;
			}else if( $inAdmin === false){
				return true;
			}else{
				return false;
			}
		}
	}


	public static function login($login, $password)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users A 
			INNER JOIN tb_persons B 
			ON A.idperson = B.idperson
			WHERE A.deslogin = :LOGIN",
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

			$data['desperson'] = utf8_encode($data['desperson']);

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
		if (!User::checkLogin($inAdmin)) {

			if ($inAdmin) {
				header("Location: /admin/login");
				exit;
			} else if (!$inAdmin) {
				header("Location: /login");
				exit;
			}
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
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
					":despassword"=>User::getPasswordHash($this->getdespassword()),
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
                    ":despassword"=>User::getPasswordHash($this->getdespassword()),
                    ":desemail"=>$this->getdesemail(),
                    ":nrphone"=>$this->getnrphone(),
                    ":inadmin"=>$this->getinadmin()
                ));

			$this->setData($results[0]);

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());	
		}
	}

    public function updateUserPersonalData()
    {

        $sql = new Sql();
        try {
            $results = $sql->select("UPDATE tb_user",
                array(
                    ":iduser"=>$this->getiduser(),
                    ":desperson"=>$this->getdesperson(),
                    ":deslogin"=>$this->getdeslogin(),
                    ":despassword"=>User::getPasswordHash($this->getdespassword()),
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

	public static function getForgotten($email, $inAdmin = true)
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
					if($inAdmin === true){
						$link = "http://ecommerce.local.com/admin/forgot/reset?code=$encyptedData";
					}else if($inAdmin === false){
						$link = "http://ecommerce.local.com/forgot/reset?code=$encyptedData";
					}
					

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

	public static function setMsgError(string $msg){
		$_SESSION[User::SESSION_ERROR_USER] = $msg;
	}

	public static function getMsgError(){
		$msg = (isset($_SESSION[User::SESSION_ERROR_USER])) ? $_SESSION[User::SESSION_ERROR_USER] : '';

		User::clearMsgError();

		return $msg;
	}

	public static function clearMsgError(){
		unset($_SESSION[User::SESSION_ERROR_USER]);
	}

    public static function setMsg(string $msg){
        $_SESSION[User::SESSION_MESSAGE_USER] = $msg;
    }

    public static function getMsg(){
        $msg = (isset($_SESSION[User::SESSION_MESSAGE_USER])) ? $_SESSION[User::SESSION_MESSAGE_USER] : '';

        User::clearMsgError();

        return $msg;
    }
    public static function clearMsg(){
        unset($_SESSION[User::SESSION_MESSAGE_USER]);
    }

	public static function getPasswordHash($password){
		return password_hash($password, PASSWORD_DEFAULT, ['cost'=>12]);
	}

	public static function checkLoginExists(string $login):bool{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin",
			[
				':deslogin'=>$login
			]);

		return (count($results) > 0);
	}

	public function getOrders(){
        $sql = new Sql();

        $results = $sql->select("
                                SELECT * FROM tb_orders a 
                                INNER JOIN tb_ordersstatus b USING(idstatus) 
                                INNER JOIN tb_carts c USING(idcart)
                                INNER JOIN tb_users d ON d.iduser = a.iduser
                                INNER JOIN tb_addresses e USING(idaddress)
                                INNER JOIN tb_persons f ON f.idperson = d.idperson
                                WHERE a.iduser = :iduser 
                                ",
            [
                ":iduser"=>$this->getiduser()
            ]);

        if(count($results)>0){
            return $results;
        }
    }

    public static function getPage(int $page = 1, int $itemsPerPage = 10){

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_users a  INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson
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
			FROM tb_users a  INNER JOIN tb_persons b USING(idperson) 
			WHERE b.desperson LIKE :search OR b.desemail LIKE :search OR a.deslogin LIKE :search
			ORDER BY b.desperson 
			LIMIT $start, $itemsPerPage;
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