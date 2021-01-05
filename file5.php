<?php

require_once "VerificationClass.php";

class Fifth extends Verify
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    protected $addr;
    protected $cnumber;
    protected $cvalid;
    protected $cvv;
    
    protected $account;
    
    public function __construct()
    {
        require_once "pdoshop.php";
      
        $this->addr = htmlspecialchars($_POST['address1']);
        $this->cnumber = htmlspecialchars($_POST['cardnumber1']);
        $this->cvalid = htmlspecialchars($_POST['cardvalid1']);
        $this->cvv = htmlspecialchars($_POST['cardcvv1']);
      
        $this->account = "45876321984025103697";
    }
  
    ///////////////////////////////////
    
    protected function verification()
    {
        $this->requiredFieldsVerification();
        $this->verifyNumbers(16, $this->cnumber);
        $this->verifyNumbers(4, $this->cvalid);
        $this->verifyNumbers(3, $this->cvv);
    }
    
    ///////////////////////////////////
    
    protected function enterData()
    {
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
      
        session_start();
  
        // Вносим данные в PaymentsT
        $statementPaymentsT = $pdoVar->prepare("UPDATE PaymentsT SET AddressUser = :AddressUser, FullPrice = :FullPrice, CardNumber = :CardNumber, ValidTo = :ValidTo, CvvCode = :CvvCode, ShopAccount = :ShopAccount WHERE LoginUser = :LoginUser");
        $statementPaymentsT->bindValue(':AddressUser', $this->addr);
        $statementPaymentsT->bindValue(':FullPrice', $_SESSION['someprice']);
        $statementPaymentsT->bindValue(':CardNumber', $this->cnumber);
        $statementPaymentsT->bindValue(':ValidTo', $this->cvalid);
        $statementPaymentsT->bindValue(':CvvCode', $this->cvv);
        $statementPaymentsT->bindValue(':ShopAccount', $this->account);
        $statementPaymentsT->bindValue(':LoginUser', $_SESSION['somevalue']);
        $statementPaymentsT->execute();
  
        $pdoVar = null;
      
        /* Из таблицы PaymentsT надо дальше передать данные в банк. Банк - это другая организация, у которой, вообще говоря, нет доступа к базе магазина. Поэтому передадим данные в банк в нашем программном комплексе, например, с помощью сессий */
        $_SESSION['somecnumber'] = $this->cnumber;
        $_SESSION['somecvalid'] = $this->cvalid;
        $_SESSION['somecvv'] = $this->cvv;
        $_SESSION['someaccount'] = $this->account;
    }
    
    ///////////////////////////////////
    
    public function moveData()
    {
        // Переместим данные в таблицу службы доставки
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
      
        // Сессии уже запущены
  
        // Установим единую дату платежа для таблицы DeliveryT
        $dateOfPay = date("Y-m-d H:i:s");
      
        // Прежде всего извлечём из базы необходимую нам информацию для таблицы DeliveryT
        $statementT = $pdoVar->prepare("SELECT CartT.NameGood, CartT.AmountGood FROM CartT WHERE CartT.LoginUser = :LoginUser");
        $statementT->bindValue(':LoginUser', $_SESSION['somevalue']);
        $statementT->execute();
      
        while (($stroka = ($statementT->fetch())) !== false) {
            // Подготовка транзакции из двух запросов к базе
            $statementDeliveryT = $pdoVar->prepare("INSERT INTO DeliveryT (LoginUser, AddressUser, NameGood, AmountGood, DatePay, IndexDeliv) VALUES (:LoginUser, :AddressUser, :NameGood, :AmountGood, :DatePay, :IndexDeliv)");
            $statementCartT = $pdoVar->prepare("DELETE FROM CartT WHERE LoginUser = :LoginUser AND NameGood = :NameGood");
      
            // Начало транзакции
            $pdoVar->beginTransaction();
      
            // Передача параметров и подготовка к выполнению первого запроса
            $statementDeliveryT->bindValue(':LoginUser', $_SESSION['somevalue']);
            $statementDeliveryT->bindValue(':AddressUser', $this->addr);
            $statementDeliveryT->bindValue(':NameGood', $stroka['NameGood']);
            $statementDeliveryT->bindValue(':AmountGood', $stroka['AmountGood']);
            $statementDeliveryT->bindValue(':DatePay', $dateOfPay);
            $statementDeliveryT->bindValue(':IndexDeliv', 0);
            $statementDeliveryT->execute();
          
        
            // Передача параметров и подготовка к выполнению второго запроса
            $statementCartT->bindValue(':LoginUser', $_SESSION['somevalue']);
            $statementCartT->bindValue(':NameGood', $stroka['NameGood']);
            $statementCartT->execute();
          
            // Выполнение транзакции
            $pdoVar->commit();
        }
  
        $pdoVar = null;
    }
    
    ///////////////////////////////////
    
    // Проверка введённых в форме авторизации данных
    public function verFunction()
    {
        // Проверим на ошибки введённые данные
        $this->verification();
        
        // Внесём полученные данные в базу
        $this->enterData();
    }
// Окончание класса Fifth
}

  ///////////////////////////////////  ///////////////////////////////////  ///////////////////////////////////  ///////////////////////////////////

class Sixth extends Verify
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    public function __construct()
    {
        require_once "pdobank.php";
    }
  
    ///////////////////////////////////
    
    public function bankFunction()
    {
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
      
        // Сессии уже запущены
  
        // Вносим данные в BankT
        $statementBankT = $pdoVar->prepare("INSERT INTO BankT (FullPrice, CardNumber, ValidTo, CvvCode, ShopAccount) Values (:FullPrice, :CardNumber, :ValidTo, :CvvCode, :ShopAccount)");
        $statementBankT->bindValue(':FullPrice', $_SESSION['someprice']);
        $statementBankT->bindValue(':CardNumber', $_SESSION['somecnumber']);
        $statementBankT->bindValue(':ValidTo', $_SESSION['somecvalid']);
        $statementBankT->bindValue(':CvvCode', $_SESSION['somecvv']);
        $statementBankT->bindValue(':ShopAccount', $_SESSION['someaccount']);
        $statementBankT->execute();
  
        $pdoVar = null;
  
        if (rand(1, 100) <= 25) {
            return 0;
        } else {
            return 1;
        }
    }
    
    ///////////////////////////////////
// Окончание класса Sixth 
}

$varShop = new Fifth();
$varShop->verFunction();

$varBank = new Sixth();
if ($varBank->bankFunction() === 0) {
    /* Сделаем перенаправление в браузере на другую страницу, ибо иначе возврат назад методами браузера может привести к повторной попытке оплаты, а это нежелательно: клиент должен вернуться назад и внести данные платежа снова, потому что банк вернул отказ оплаты */
    header("Location: redirectfile5false.php");
    exit;
} else {
    $varShop->moveData();
    /* Сделаем перенаправление в браузере на другую страницу, ибо иначе возврат назад методами браузера может привести к повторной оплате уже оплаченных товаров */
    header("Location: redirectfile5true.php");
    exit;
}
