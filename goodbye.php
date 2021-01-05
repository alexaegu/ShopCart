<?php

class ExitOne
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    public function __construct()
    {
        require_once "pdoshop.php";
    }
  
    ///////////////////////////////////
    
    public function exitFromTheCart()
    {
        echo "<html> \n <head> \n <title> \n";
        echo "Выход из магазина";
        echo "</title> \n <meta charset = \"utf-8\">";
        echo "</head> \n <body> \n";
      
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO ($dsn, $this->username, $this->passw);
      
        require_once "verifyauthorization.php";
  
        // Сессии уже запущены
        
        // Введём запрос для обновления IndexAut данного пользователя
        $statement = $pdoVar->prepare("UPDATE UsersT SET IndexAut = 0 WHERE LoginUser = :Login1");
        $statement->bindValue(':Login1', $_SESSION['somevalue']);
        $statement->execute();
      
        echo "До новых встреч! </br>";
        echo "<a href = \"loginform.html\"> Ссылка на страницу входа / авторизации </a>";
        echo "</body> \n </html> \n";
      
        $pdoVar = null;
      
        unset($_SESSION);
        session_destroy();
    }
}

$var = new ExitOne();
$var->exitFromTheCart();
