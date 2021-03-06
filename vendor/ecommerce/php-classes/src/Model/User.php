<?php
namespace thiagova\Model;
use \thiagova\DB\Sql;
use \thiagova\Model;
use \thiagova\Mailer;

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";

    public static function login ($login, $password){
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));
        if (count($results) === 0){
            throw new \Exception("Usuário ou senha inválido.");
        }
        $data = $results[0];
        if(password_verify($password, $data["despassword"]) === true){
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;

        } else {
            throw new \Exception("Usuário ou senha inválido.");
        }
    }

    public static function verifyLogin($inadmin = true){
        if (
            !isset($_SESSION[User::SESSION])                        // Verifica se a variavel sessão não foi definida
        ||  !$_SESSION[User::SESSION]                               // Verifica se a variavel de sessão não exist
        ||  !(int)$_SESSION[User::SESSION]["iduser"]>0              // Verifica se o id do usuario dessa sessão não é maior que 0
        ||  (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin   // Verifica se o usuaŕio não é administrador
        )    
        {
            header("Location: /admin/login");                       // Redireciona para página de login
            exit;
        } 
    }

    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll(){
        $sql = new Sql();
        return $sql->select("SELECT * from tb_users a inner join tb_persons b using (idperson) order by b.desperson");
    }

    public function save (){
        $sql = new Sql();
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>password_hash($this->getdespassword(), PASSWORD_DEFAULT,["cost"=>12]),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }

    public function get ($iduser){
        $sql = new Sql();
        $results = $sql->select("SELECT * from tb_users a inner join tb_persons b using (idperson) where a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    public function update (){
        $sql = new Sql();
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));
        $this->setData($results[0]);
    }
    
    public function delete(){
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email){
        $sql = new Sql();
        $results = $sql->select("SELECT * from tb_persons a
        inner join tb_users b using(idperson)
        where a.desemail = :email", Array(
            ":email"=>$email
        ));
        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha.");
        } else {
            $data = $results[0];

            $resultsRecovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", Array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($resultsRecovery) === 0){
                throw new \Exception("Não foi possível recuperar a senha.");
            } else {
                $dataRecovery = $resultsRecovery[0];
                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
                $link = "http://localhost:8081/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinição de Senha", "forgot", Array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                ));
                $mailer->send();
                return $data;
            }
        }
    }

    public static function ValidForgotDecrypt($code){
        $idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
        INNER JOIN tb_users b USING(iduser)
        INNER JOIN tb_persons c USING(idperson)
        WHERE a.idrecovery = :idrecovery
        AND a.dtrecovery is null
        AND DATE_ADD(a.dtregister, interval 1 hour) >= now()
        ", Array(
            ":idrecovery" => $idrecovery
        ));
        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha");
        } else {
            return $results[0];
        }
    }

    public static function setForgotUsed ($idrecovery){
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswrodsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery",array(
            ":idrecovery"=>$idrecovery
        ));
    }

    public function setPassword($password){
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despasswrod = :password WHERE iduser = :iduser",array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));
    }
}
