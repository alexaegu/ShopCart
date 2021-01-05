<?php

require_once "VerificationClass.php";

class RedirectFirst extends Verify
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
    
    // Категории товаров
    public function catFunction()
    {
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
        
        require_once "verifyauthorization.php";
    
        // Выбрать все те категории из CategoryT, для которых существуют товары на складе магазина, то есть для которых не равно нулю QuantityGood
        $statement = $pdoVar->prepare("SELECT DISTINCT NameCat FROM CategoryT INNER JOIN GoodsT WHERE GoodsT.QuantityGood != 0 AND CategoryT.NumberCat = GoodsT.NumberCat");
        $statement->execute();
    
        echo "<html> \n <head> \n";
        echo "<title> Форма выбора категории товаров </title> \n <meta charset = \"utf-8\"> \n <link rel = \"stylesheet\" href = \"ourstyles.css\"> \n";
        echo "</head> \n";
        echo "<body> \n";
        
        $stroka = $statement->fetch();
        if ($stroka === false) {
            // Первый же запуск fetch() вернул пустой результат, значит товаров в магазине нет вообще
            echo "<table id = \"indtable\"> \n";
            echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Форма выбора категории товаров </strong> </td> \n </tr> \n";
            echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> К сожалению, в данный момент в магазине отсутствуют товары для продажи. Ожидайте поступления товаров, а сейчас, пожалуйста, перейдите в свою корзину, если ранее она у вас была заполнена товарами и ожидает завершения покупки, или выйдите из магазина.</br> </td> \n </tr> \n </table> \n";
            echo "<form action = \"gotocart.php\" method = \"post\"> \n";
            echo "<table id = \"indtable\"> \n";
            echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Корзина\" id = \"indcolor\"> </td> \n </tr> \n";
            echo "</table> \n";
            echo "</form> \n";
            echo "<form action = \"goodbye.php\" method = \"post\"> \n";
            echo "<table id = \"indtable\"> \n";
            echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
            echo "</table> \n";
            echo "</form> \n";
            echo "</body> \n </html>";
          
            $pdoVar = null;
            exit;
        }
    
        // Сюда заходит, если товары в магазине есть, потому что если товаров нет вообще, то выше см. - окончание работы с помощью exit
        echo "<form action = \"file2.php\" method = \"post\"> \n";
        echo "<table id = \"indtable\"> \n";
        echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Форма выбора категории товаров </strong> </td> \n </tr> \n";
        echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> Выберите нужную категорию товара и перейдите к выбору товаров. </br> Или перейдите сразу в свою корзину. </br> Или сделайте выход из магазина.</br> </td> \n </tr> \n";
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
          
        do {
            /* Так как запрос выбирает название категорий NameCat, то в этом цикле создаётся единая группа переключателей (единство группы определяется с помощью name="ch1"), передающая значение выбранной NameCat (с помощью значения value) в дальнейший файл file2.php для обработки */
            // Каждому переключателю присваивается соответствующее значение value="Название_Категории_Товаров"
            echo "<tr> \n <td style = \"text-align: left; height: 35px\"> <input type = \"radio\" name = \"ch1\" value = \"".$stroka['NameCat']."\">". $stroka['NameCat'] ."<br> </td> \n </tr> \n";
        } while (($stroka = $statement->fetch()) !== false);
        
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\">";
        echo "<input type = \"submit\" value = \"Перейти к выбору товаров\" id = \"indcolor\">";
        echo "</td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
            
        echo "<form action = \"gotocart.php\" method = \"post\"> \n";
        echo "<table id = \"indtable\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Корзина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
            
        echo "<form action = \"goodbye.php\" method = \"post\"> \n";
        echo "<table id = \"indtable\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
        echo "</body> \n </html>";
        
        $pdoVar = null;
    }
}

$var = new RedirectFirst();
$var->catFunction();
