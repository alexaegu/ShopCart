<?php

class Eighth
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
    
    public function theFunction()
    {
        require_once "verifyauthorization.php";  
      
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
    
        // Сессии уже запущены
    
        // Обнуляем данные в PaymentsT, которые требуется обнулить если оплата прошла
        $statementPaymentsT = $pdoVar->prepare("UPDATE PaymentsT SET AddressUser = NULL, FullPrice = NULL, CardNumber = NULL, ValidTo = NULL, CvvCode = NULL, ShopAccount = NULL WHERE LoginUser = :LoginUser");
        $statementPaymentsT->bindValue(':LoginUser', $_SESSION['somevalue']);
        $statementPaymentsT->execute();
      
        $pdoVar = null;
        
        echo "<html> \n <head> \n";
        echo "<title> Информация об оплате товаров </title> \n <meta charset = \"utf-8\"> \n <link rel = \"stylesheet\" href = \"ourstyles.css\"> \n";  
        echo "</head> \n";
        echo "<body> \n";
        echo "<table> \n";
        echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Возвращённая из банка информация об оплате товаров </strong> </td> \n </tr> \n";
        echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> Ваша оплата получена, товары переданы в службу доставки и в ближайшее время будут доставлены по указанному адресу. Благодарим Вас за выбор нашего магазина! </br> </td> \n </tr> \n </table> \n";
        echo "<form action = \"goodbye.php\" method = \"post\"> \n";
        echo "<table> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
        echo "</body> \n </html>";
    }
}

$var8 = new Eighth();
$var8->theFunction();
