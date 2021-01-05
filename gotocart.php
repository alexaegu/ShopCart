<?php

require_once "VerificationClass.php";

class CartClass extends Verify
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    // Введём счётчик пар "флажок-поле", которые идут на удаление из корзины
    protected $j;
  
    public function __construct()
    {
        require_once "pdoshop.php";
  
        // Так как $j используется в той части программы, где предполагается, что заведомо есть хотя бы одна пара "флажок-поле", то присваиваем ему предварительное значение 1
        $this->j = 1;
    }
  
    ///////////////////////////////////
  
    public function showCart()
    {
        // Показать корзину
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
    
        require_once "verifyauthorization.php";
    
        // Сессии уже запущены
        
        echo "<!DOCTYPE html> \n";
        echo "<html> \n";
        echo "<head> \n";
        echo "<title>Форма корзины</title> \n";
        echo "<meta charset = \"utf-8\"> \n";
        echo "<link rel = \"stylesheet\" href = \"ourstyles.css\"> \n";
        echo "</head> \n";
        echo "<body> \n";
    
        // Выбрать все товары из CartT для данного пользователя, с учётом их цен и доступных товаров к продаже
        $statement = $pdoVar->prepare("SELECT DISTINCT CartT.NameGood, AmountGood, PriceGood, QuantityGood FROM CartT INNER JOIN GoodsT WHERE CartT.NameGood = GoodsT.NameGood AND CartT.LoginUser = :LoginUser");
        $statement->bindValue(':LoginUser', $_SESSION['somevalue']);
        $statement->execute();
    
        $stroka = $statement->fetch();
        if ($stroka === false) {
            // Первый же запуск fetch() вернул пустой результат, значит товаров в корзине нет вообще
            echo "<table> \n";
            echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Ваша корзина </strong> </td> \n </tr> \n";
            echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> В данный момент в вашей корзине отсутствуют товары.</br> Вернитесь назад и выберите товары для покупки или выйдите из магазина.</br> </td> \n </tr> \n </table> \n";
            echo "<form action = \"goodbye.php\" method = \"post\"> \n";
            echo "<table> \n";
            echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
            echo "</table> \n";
            echo "</form> \n";
            
            $pdoVar = null;
            exit;
        }
    
        // Сюда заходит, если товары в корзине есть
        echo "<form action = \"deletefromcart.php\" method = \"post\"> \n";
        echo "<strong id = \"indcolor\"> Ваша корзина </strong> </br> </br> \n";
        echo "<span id = \"helptext\"> Обратите внимание, что количество заказываемого товара ограничено разрешённым числом одновременного заказа. Если вы желаете купить больше единиц некоторого товара, но добавить их в корзину не можете, то сначала оплатите то, что уже есть в вашей корзине.</br> \n";
        echo " Если вы желаете удалить часть товаров из корзины, отметьте эти товары флажками и внесите количество на удаление. Затем нажмите кнопку \"Удалить выбранные товары\". </span> </br> \n";
          
        echo "<table> \n";
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
        
        echo "<thead style = \"height: 50px; transform: translateY(25px)\"> <tr> <td> <strong id = \"indcolor\"> Название товара </strong> </td> <td style = \"transform: translateX(25px)\"> <strong id = \"indcolor\"> Цена единицы </br> товара </strong> </td> <td style = \"transform: translateX(50px)\"> <strong id = \"indcolor\"> Вы покупаете </br> единиц </strong> </td> <td style = \"transform: translateX(75px)\"> <strong id = \"indcolor\"> Удаляемое </br> количество </strong> </td> </tr> </thead> \n";
        
        // Общая стоимость покупки - $thePrice
        $thePrice = 0;
        
        do {
            /* Так как запрос выбирает товары NameGood, то в этом цикле создаются флажки, передающие значения выбранных NameGood (с помощью значения value) в дальнейший файл file3.php для обработки */
            /* Каждому флажку присваивается соответствующее значение value=NameGood; каждой паре "флажок-поле" присваивается отдельный массив chb$j[]. То есть, например, у первой пары "флажок-поле" массив chb1[], у второй пары "флажок-поле" массив chb2[] и т.д. */
            echo "<tr> \n <td style = \"text-align: left; height: 35px\"> <input type = \"checkbox\" name = \"chb".$this -> j."[]\" value = \"".$stroka['NameGood']."\">". $stroka['NameGood'] ."<br> </td> \n";
            echo "<td style = \"text-align: left; height: 35px; transform: translateX(25px)\">". $stroka['PriceGood'] ."<br> </td> \n";
            echo "<td style = \"text-align: left; height: 35px; transform: translateX(50px)\">". $stroka['AmountGood'] ."<br> </td> \n";
            /* Имя поля используется такое же, как у флажка ("chb$j[]"), потому что если флажок установлен, то дальнейшая проверка соответствующего его имени поля идёт по следующему индексу в массиве chb$j[] (так как поле, соответствующее флажку, следует сразу же за флажком) */
            echo "<td style = \"text-align: left; height: 35px; transform: translateX(75px)\"> <input style = \"width: 120px\" type = \"number\" name = \"chb".$this -> j."[]\" value = \"0\" min = \"0\"> <br> </td> \n";
            echo "</tr> \n";
            
            $thePrice += ($stroka['AmountGood'] * $stroka['PriceGood']);
            $this->j++;
        } while (($stroka = $statement->fetch()) !== false);
        
        echo "<tr style = \"height: 50px; transform: translateY(25px)\"> <td> <strong id = \"indcolor\"> Сумма вашей покупки равна: </strong> </td> <td style = \"transform: translateX(25px)\"> <strong id = \"indcolor\"> ".$thePrice." </strong> </td> </tr> \n";
    
        // Передача с помощью сессий стоимости покупки
        $_SESSION['someprice'] = $thePrice;
        
        echo "<tr> \n <td style = \"height: 25px\"> </td> \n </tr> \n";
        
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\">";
        echo "<input type = \"submit\" value = \"Удалить выбранные товары\" id = \"indcolor\">";
        echo "</td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
            
        echo "<form action = \"file4.php\" method = \"post\"> \n";
        echo "<table style = \"text-align: left\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Оплатить товары в корзине\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
    
        echo "<form action = \"goodbye.php\" method = \"post\"> \n";
        echo "<table style = \"text-align: left\"> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
        echo "</form> \n";
        
        echo "</body> \n";
        echo "</html> \n";
    
        $pdoVar = null;
    }
}

$var = new CartClass();
$var->showCart();
