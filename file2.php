<?php

require_once "VerificationClass.php";

class Second extends Verify
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    protected $RadioName;
    
    // Введём счётчик существующих пар "флажок-поле"
    protected $j;
    
    public function __construct()
    {
        require_once "pdoshop.php";
      
        /* В случае, если пользователь не выберет категорию товара, представленную в file1.php набором переключателей с name="ch1", выражение $_POST['ch1'] вернёт Notice "Undefined index: ch1". Чтобы этого не произошло, применим оператор управления ошибками. В случае, если эта ошибка возникнет, она будет обработана в соответствующей функции */
        $this->RadioName = @$_POST['ch1'];
      
        // Так как $j используется в той части программы, где предполагается, что заведомо есть хотя бы одна пара "флажок-поле", то присваиваем ему предварительное значение 1
        $this->j = 1;
    }
  
    ///////////////////////////////////
    
    protected function verification()
    {
      $this->requiredRadiosVerification($this->RadioName);
    }
    
    ///////////////////////////////////
    
    // Выбор товаров данной категории
    public function goodFunction()
    {
        // Проверим на ошибки введённые данные
        $this->verification();
          
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
    
        require_once "verifyauthorization.php";
    
        // Выбрать все те товары из GoodsT, для которых не равно нулю QuantityGood и для которых указано выбранное пользователем имя категории
        $statement = $pdoVar->prepare("SELECT NameGood, PriceGood FROM GoodsT INNER JOIN CategoryT WHERE GoodsT.QuantityGood != 0 AND CategoryT.NameCat = :NameCat1 AND CategoryT.NumberCat = GoodsT.NumberCat");
        $statement->bindValue(':NameCat1', $this->RadioName);
        $statement->execute();
    
        echo "<html> \n <head> \n";
        echo "<title> Форма выбора товаров данной категории </title> \n <meta charset = \"utf-8\"> \n <link rel = \"stylesheet\" href = \"ourstyles.css\"> \n";
        
        echo "</head> \n";
        echo "<body> \n";
        
        $stroka = $statement->fetch();
        if ($stroka === false) {
            // Первый же запуск fetch() вернул пустой результат, значит товаров этой категории в магазине нет вообще
            echo "<table> \n";
            echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Форма выбора товаров данной категории </strong> </td> \n </tr> \n";
            echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> К сожалению, в данный момент в магазине отсутствуют товары выбранной категории. Ожидайте поступления товаров, а сейчас, пожалуйста, перейдите в свою корзину, если ранее она у вас была заполнена товарами и ожидает завершения покупки, или выйдите из магазина, или вернитесь назад и выберите другую категорию.</br> </td> \n </tr> \n </table> \n";
            echo "<form action = \"gotocart.php\" method = \"post\"> \n";
            echo "<table> \n";
            echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Корзина\" id = \"indcolor\"> </td> \n </tr> \n";
            echo "</table> \n";
            echo "</form> \n";
            echo "<form action = \"goodbye.php\" method = \"post\"> \n";
            echo "<table> \n";
            echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
            echo "</table> \n";
            echo "</form> \n";
            echo "</body> \n </html>";
          
            $pdoVar = null;
            exit;
        }
    
        // Сюда заходит, если товары данной категории в магазине есть
        echo "<form action = \"file3.php\" method = \"post\"> \n";
        echo "<strong id = \"indcolor\"> Форма выбора товаров данной категории </strong> </br> </br> \n";
        echo " <p id = \"helptext\"> Выберите нужные товары, отметив флажками, внесите количество каждого, которое вы хотите купить. </br> Или перейдите сразу в свою корзину. </br> Или сделайте выход из магазина.</br> </p> \n";
          
        echo "<table> \n";
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
        
        echo "<thead style = \"height: 50px; transform: translateY(25px)\"> <tr> <td> <strong id = \"indcolor\"> Название товара </strong> </td> <td style = \"transform: translateX(25px)\"> <strong id = \"indcolor\"> Цена единицы товара </strong> </td> <td style = \"transform: translateX(50px)\"> <strong id = \"indcolor\"> Заказываемое количество </strong> </td> </tr> </thead> \n";
        
        do {
            /* Так как запрос выбирает товары NameGood, то в этом цикле создаются флажки, передающие значения выбранных NameGood (с помощью значения value) в дальнейший файл file3.php для обработки */
            /* Каждому флажку присваивается соответствующее значение value=NameGood; каждой паре "флажок-поле" присваивается отдельный массив chb$j[]. То есть, например, у первой пары "флажок-поле" массив chb1[], у второй пары "флажок-поле" массив chb2[] и т.д. */
            echo "<tr> \n <td style = \"text-align: left; height: 35px\"> <input type = \"checkbox\" name = \"chb".$this -> j."[]\" value = \"".$stroka['NameGood']."\">". $stroka['NameGood'] ."<br> </td> \n";
            echo "<td style = \"text-align: left; height: 35px; transform: translateX(25px)\">". $stroka['PriceGood'] ."<br> </td> \n";
            /* Имя поля используется такое же, как у флажка ("chb$j[]"), потому что если флажок установлен, то дальнейшая проверка соответствующего его имени поля идёт по следующему индексу в массиве chb$j[] (так как поле, соответствующее флажку, следует сразу же за флажком) */
            echo "<td style = \"text-align: left; height: 35px; transform: translateX(50px)\"> <input style = \"width: 120px\" type = \"number\" name = \"chb".$this -> j."[]\" value = \"1\" min = \"1\"> <br> </td> \n";
            echo "</tr> \n";
            
            $this->j++;
        } while (($stroka = $statement->fetch()) !== false);
        
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
        
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\">";
        echo "<input type = \"submit\" value = \"Добавить выбранные товары в корзину\" id = \"indcolor\">";
        echo "</td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
            
        echo "<form action = \"gotocart.php\" method = \"post\"> \n";
        echo "<table style = \"text-align: left\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Корзина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
            
        echo "<form action = \"goodbye.php\" method = \"post\"> \n";
        echo "<table style = \"text-align: left\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
        echo "</body> \n </html>";
        
        $pdoVar = null;
    }
}

$var = new Second();
$var->goodFunction();
