<?php

require_once "VerificationClass.php";

class Third extends Verify
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
    
    protected function verification()
    {
        $this->requiredFlagsVerification();
    }
    
    ///////////////////////////////////
    
    // Добавление товаров в корзину
    public function addToCart()
    {
        // Проверим на ошибки введённые данные
        $this->verification();
        
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
    
        // Запустим сессии для передачи логина авторизованного пользователя
        session_start();
        
        // Необходимо предварительно вставить значение LoginUser в PaymentsT
        $statementPaymentsT = $pdoVar->prepare("INSERT INTO PaymentsT (LoginUser) VALUES (:LoginUser) ON DUPLICATE KEY UPDATE LoginUser = :LoginUser");
        $statementPaymentsT->bindValue(':LoginUser', $_SESSION['somevalue']);
        $statementPaymentsT->execute();
        
        $j = 1; // Счётчик всех пар "флажок-поле": установленных и неустановленных
        
        while (isset($_POST["chb$j"])) {
            $opts = $_POST["chb$j"];
          
            if (count($opts) == 2) {
                // Добавлен ли товар :NameGood уже в корзину? Если добавлен, будем обновлять для него запись; если не добавлен, будем вставлять новую запись в корзину
                $statement = $pdoVar->prepare("SELECT COUNT(*) FROM CartT WHERE NameGood = :NameGood AND LoginUser = :LoginUser");
                $statement->bindValue(':NameGood', $opts[0]);
                $statement->bindValue(':LoginUser', $_SESSION['somevalue']);
                $statement->execute();
                // $parameter равен 0 - запись вставляем, равен 1 - запись обновляем
                $parameter = ($statement->fetchColumn() === '0') ? 0 : 1;
            
                // Проверяем: сколько товаров пожелал купить покупатель. Желаемое количество заказываемых товаров :DesiredValue. Оно должно быть <= QuantityGood && <= PermittedQuantity
                $statement = $pdoVar->prepare("SELECT COUNT(*) FROM GoodsT WHERE NameGood = :NameGood AND :DesiredValue <= QuantityGood AND :DesiredValue <= PermittedQuantity");
                $statement->bindValue(':NameGood', $opts[0]);
                $statement->bindValue(':DesiredValue', $opts[1]);
                $statement->execute();
            
                // Если покупатель пожелал товаров больше, чем разрешено купить одномоментно или больше, чем есть в наличии, то добавляем ему в корзину максимально возможное число
                if ($statement->fetchColumn() === '0') {
                    // Подготовка транзакции из трёх запросов к базе
                    $statementGoodsT = $pdoVar->prepare("SELECT QuantityGood, PermittedQuantity FROM GoodsT WHERE NameGood = :NameGood");
                    if ($parameter === 0) {
                        $statementCartT = $pdoVar->prepare("INSERT INTO CartT (LoginUser, NameGood, AmountGood) VALUES (:LoginUser, :NameGood, :AmountGood)");
                    } else {
                        $statementCartT = $pdoVar->prepare("UPDATE CartT SET AmountGood = AmountGood + :AmountGood WHERE NameGood = :NameGood AND LoginUser = :LoginUser");
                    }
                    $statementGoodsT2 = $pdoVar->prepare("UPDATE GoodsT SET QuantityGood = QuantityGood - :AmountGood WHERE NameGood = :NameGood");
              
                    // Начало транзакции
                    $pdoVar->beginTransaction();
              
                    // Передача параметров и подготовка к выполнению первого запроса
                    $statementGoodsT->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT->execute();
                    // Выберем минимальное число между QuantityGood и PermittedQuantity  
                    $stroka = $statementGoodsT->fetch();
                    $minChislo = ($stroka['QuantityGood'] < $stroka['PermittedQuantity']) ? $stroka['QuantityGood'] : $stroka['PermittedQuantity'];
              
                    // Передача параметров и подготовка к выполнению второго запроса
                    $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
                    $statementCartT->bindValue(':NameGood', $opts[0]);
                    $statementCartT->bindValue(':AmountGood', $minChislo);
                    $statementCartT->execute();
              
                    // Передача параметров и подготовка к выполнению третьего запроса
                    $statementGoodsT2->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT2->bindValue(':AmountGood', $minChislo);
                    $statementGoodsT2->execute();
              
                    // Выполнение транзакции
                    $pdoVar->commit();
                } else {
                    // Иначе, если покупатель пожелал товаров НЕ больше, чем разрешено купить одномоментно и НЕ больше, чем есть в наличии, то добавляем ему в корзину желаемое число
                    // Подготовка транзакции из двух запросов к базе
                    if ($parameter === 0) {
                        $statementCartT = $pdoVar->prepare("INSERT INTO CartT (LoginUser, NameGood, AmountGood) VALUES (:LoginUser, :NameGood, :AmountGood)");
                    } else {
                        $statementCartT = $pdoVar->prepare("UPDATE CartT SET AmountGood = AmountGood + :AmountGood WHERE NameGood = :NameGood AND LoginUser = :LoginUser");
                    }
                    $statementGoodsT2 = $pdoVar->prepare("UPDATE GoodsT SET QuantityGood = QuantityGood - :AmountGood WHERE NameGood = :NameGood");
              
                    // Начало транзакции
                    $pdoVar->beginTransaction();
              
                    // Передача параметров и подготовка к выполнению первого запроса
                    $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
                    $statementCartT->bindValue(':NameGood', $opts[0]);
                    $statementCartT->bindValue(':AmountGood', $opts[1]);
                    $statementCartT->execute();
              
                    // Передача параметров и подготовка к выполнению второго запроса
                    $statementGoodsT2->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT2->bindValue(':AmountGood', $opts[1]);
                    $statementGoodsT2->execute();
              
                    // Выполнение транзакции
                    $pdoVar->commit();
                }
            
                /* Здесь возможна ситуация, что путём постепенного добавления некоторого товара в корзину покупатель превысил в своей корзине предельно допустимое число товара для покупки. То есть он, например, добавил одну единицу некоего товара - вернулся назад - добавил ещё одну единицу этого же товара - вернулся назад - добавил ещё единицу - и так далее. В конце концов в его корзине оказалось некоего товара больше, чем разрешено и допустимо продать магазину одномоментно. Проверим это, и если это действительно так, то обновим корзину покупателя, уменьшив ему число товаров в корзине до предельно допустимой нормы. */
                $statement1 = $pdoVar->prepare("SELECT DISTINCT CartT.NameGood, AmountGood FROM CartT WHERE LoginUser = :LoginUser AND CartT.NameGood = :NameGood");
                $statement1->bindValue(':NameGood', $opts[0]);
                $statement1->bindValue(':LoginUser', $_SESSION['somevalue']);
                $statement1->execute();
            
                $statement2 = $pdoVar->prepare("SELECT DISTINCT GoodsT.NameGood, QuantityGood, PermittedQuantity FROM GoodsT WHERE GoodsT.NameGood = :NameGood");
                $statement2->bindValue(':NameGood', $opts[0]);
                $statement2->execute();
            
                $stroka1 = $statement1->fetch();
                $stroka2 = $statement2->fetch();
            
                if ($stroka1['AmountGood'] > $stroka2['PermittedQuantity']) {
                    $minChislo = ($stroka2['QuantityGood'] < $stroka2['PermittedQuantity']) ? $stroka2['QuantityGood'] : $stroka2['PermittedQuantity'];
              
                    // Надо уменьшить число товара в корзине на величину AmountGood - $minChislo, то есть фактически вместо AmountGood внести $minChislo
              
                    // Подготовка транзакции из двух запросов к базе
                    $statementCartT = $pdoVar->prepare("UPDATE CartT SET AmountGood = AmountGood - :AmountGood WHERE NameGood = :NameGood AND LoginUser = :LoginUser");
                    $statementGoodsT = $pdoVar->prepare("UPDATE GoodsT SET QuantityGood = QuantityGood + :AmountGood WHERE NameGood = :NameGood");
              
                    // Начало транзакции
                    $pdoVar->beginTransaction();
              
                    // Передача параметров и подготовка к выполнению первого запроса
                    $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
                    $statementCartT->bindValue(':NameGood', $opts[0]);
                    $statementCartT->bindValue(':AmountGood', $stroka1['AmountGood'] - $minChislo);
                    $statementCartT->execute();
              
                    // Передача параметров и подготовка к выполнению второго запроса
                    $statementGoodsT->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT->bindValue(':AmountGood', $stroka1['AmountGood'] - $minChislo);
                    $statementGoodsT->execute();
              
                    // Выполнение транзакции
                    $pdoVar->commit();
                }
            }
          
            $j++;
        }
        $pdoVar = null;
        
        /* Сделаем перенаправление в браузере на другую страницу, ибо иначе возврат назад методами браузера может привести к повторному добавлению уже добавленных товаров в корзину, а это нежелательно: добавлять надо только то, что непосредственно выбрал клиент магазина */
        header("Location: redirectfile3.php");
        exit;
    }
}

$var = new Third();
$var->addToCart();
