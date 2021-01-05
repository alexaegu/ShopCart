<?php

require_once "VerificationClass.php";

class DelCartClass extends Verify
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
        $this->verifyFlags();
    }
    
    ///////////////////////////////////
    
    // Удаление товаров из корзины
    public function delFromCart()
    {
        // Проверим на ошибки введённые данные
        $this->verification();
        
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
    
        // Запустим сессии для передачи логина авторизованного пользователя
        session_start();
        
        $j = 1; // Счётчик всех пар "флажок-поле": установленных и неустановленных
        
        while (isset($_POST["chb$j"])) {
            $opts = $_POST["chb$j"];
          
            if (count($opts) == 2) {
                // Для товара :NameGood в корзине выберем его значение :AmountGood
                $statement = $pdoVar->prepare("SELECT AmountGood FROM CartT WHERE NameGood = :NameGood AND LoginUser = :LoginUser");
                $statement->bindValue(':NameGood', $opts[0]);
                $statement->bindValue(':LoginUser', $_SESSION['somevalue']);
                $statement->execute();
                // $parameter равен 0 - запись вставляем, равен 1 - запись обновляем
                $parameter = ($statement->fetchColumn());
            
                // Проверяем: сколько товаров пожелал удалить покупатель. Желаемое количество удаляемых товаров :DeletedValue. Оно должно быть <= AmountGood
                $statement = $pdoVar->prepare("SELECT COUNT(*) FROM CartT WHERE NameGood = :NameGood AND :DeletedValue <= AmountGood AND LoginUser = :LoginUser");
                $statement->bindValue(':NameGood', $opts[0]);
                $statement->bindValue(':DeletedValue', $opts[1]);
                $statement->bindValue(':LoginUser', $_SESSION['somevalue']);
                $statement->execute();
            
                // Если покупатель пожелал удалить товаров больше, чем у него есть в корзине, то удаляем все его товары из корзины (товары NameGood)
                if ($statement->fetchColumn() === '0') {
                    // Подготовка транзакции из двух запросов к базе
                    $statementCartT = $pdoVar->prepare("DELETE FROM CartT WHERE LoginUser = :LoginUser AND NameGood = :NameGood");
                    $statementGoodsT2 = $pdoVar->prepare("UPDATE GoodsT SET QuantityGood = QuantityGood + :AmountGood WHERE NameGood = :NameGood");
              
                    // Начало транзакции
                    $pdoVar->beginTransaction();
              
                    // Передача параметров и подготовка к выполнению первого запроса
                    $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
                    $statementCartT->bindValue(':NameGood', $opts[0]);
                    $statementCartT->execute();
              
                    // Передача параметров и подготовка к выполнению второго запроса
                    $statementGoodsT2->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT2->bindValue(':AmountGood', $parameter);
                    $statementGoodsT2->execute();
              
                    // Выполнение транзакции
                    $pdoVar->commit();
                } else {
                    // Иначе, если покупатель пожелал удалить товаров НЕ больше, чем у него есть в корзине, то удаляем то количество товаров NameGood, которое он пожелал удалить
                    // Подготовка транзакции из двух запросов к базе
                    $statementCartT = $pdoVar->prepare("DELETE FROM CartT WHERE LoginUser = :LoginUser AND NameGood = :NameGood");
                    $statementGoodsT2 = $pdoVar->prepare("UPDATE GoodsT SET QuantityGood = QuantityGood + :AmountGood WHERE NameGood = :NameGood");
                    
                    // Начало транзакции
                    $pdoVar->beginTransaction();
                    
                    // Передача параметров и подготовка к выполнению первого запроса
                    $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
                    $statementCartT->bindValue(':NameGood', $opts[0]);
                    $statementCartT->execute();
                    
                    // Передача параметров и подготовка к выполнению второго запроса
                    $statementGoodsT2->bindValue(':NameGood', $opts[0]);
                    $statementGoodsT2->bindValue(':AmountGood', $opts[1]);
                    $statementGoodsT2->execute();
                    
                    // Выполнение транзакции
                    $pdoVar->commit();
                }
            }
          
            $j++;
        }
        $pdoVar = null;
        
        /* Сделаем перенаправление в браузере на страницу корзины, ибо иначе возврат назад методами браузера может привести к повторному удалению уже удалённых товаров из корзины, а это нежелательно: удалять надо только то, что непосредственно выбрал клиент магазина */
        header("Location: gotocart.php");
        exit;
    }
}

$var = new DelCartClass();
$var->delFromCart();
