<?php

require_once "VerificationClass.php";

class First extends Verify
{
    protected $hostname;
    protected $dbname;
    protected $username;
    protected $passw;
    protected $charset;
    
    protected $USname;
    protected $USpassword;
    
    public function __construct()
    {
        require_once "pdoshop.php";
      
        $this->USname = htmlspecialchars($_POST['name1']);
        $this->USpassword = htmlspecialchars($_POST['password1']);
    }
  
    ///////////////////////////////////
    
    protected function verification()
    {
        $this->requiredFieldsVerification();
    }
    
    ///////////////////////////////////
    
    protected function existData()
    {
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbname;charset=$this->charset";
        $pdoVar = new PDO($dsn, $this->username, $this->passw);
      
        //Запустим сессии для передачи логина авторизованного пользователя в файлы далее
        session_start();
  
        // Выясним, что именно находится в USname: логин или e-mail. Если содержит собаку, то имейл, иначе - логин
      
        // $USname - это логин    
        if (mb_strpos($this->USname, "@") === false) {
            $statement = $pdoVar->prepare("SELECT LoginUser, PasswordUser FROM UsersT WHERE LoginUser = :Login1");
            $statement->bindValue(':Login1', $this->USname);
            $statement->execute();
            $stroka = $statement->fetch();
            if (password_verify(($this->USpassword), $stroka['PasswordUser'])) {
                // Логин и пароль в форме авторизации введены правильно; обновим информацию для данного пользователя
                $statement = $pdoVar->prepare("UPDATE UsersT SET DateAut = now(), IndexAut = 1 WHERE LoginUser = :Login1");
                $statement->bindValue(':Login1', $stroka['LoginUser']);
                $statement->execute();
            
                // Передача с помощью сессий логина авторизовавшегося пользователя на дальнейшие файлы программы и таблицы БД
                $_SESSION['somevalue'] = $this->USname;
            } else {
                echo "У вас неправильно введённые данные: логин или пароль, поэтому вернитесь назад и попробуйте снова";
                exit;
            }
        } else {
            // USname - это электронный адрес
            $statement = $pdoVar->prepare("SELECT LoginUser, EmailUser, PasswordUser FROM UsersT WHERE EmailUser = :Email1");
            $statement->bindValue(':Email1', $this->USname);
            $statement->execute();
            $stroka = $statement->fetch();
            if (password_verify(($this->USpassword), $stroka['PasswordUser'])) {
                // E-mail и пароль в форме авторизации введены правильно; обновим информацию для данного пользователя
                $statement = $pdoVar->prepare("UPDATE UsersT SET DateAut = now(), IndexAut = 1 WHERE EmailUser = :Email1");
                $statement->bindValue(':Email1', $this->USname);
                $statement->execute();
            
                // Передача с помощью сессий логина авторизовавшегося пользователя на дальнейшие файлы программы и таблицы БД
                $_SESSION['somevalue'] = $stroka['LoginUser'];
            } else {
                echo "У вас неправильно введённые данные: e-mail или пароль, поэтому вернитесь назад и попробуйте снова";
                exit;
            }
        }
        $pdoVar = null;
    }
    
    ///////////////////////////////////
    
    // Проверка введённых в форме авторизации данных
    public function verFunction()
    {
        // Проверим на ошибки введённые данные
        $this->verification();
        
        // Проверим введённые данные на существование их в базе
        $this->existData();
      
        /* Сделаем перенаправление в браузере на другую страницу, ибо иначе возврат назад методами браузера может привести к повторной авторизации пользователя, который, возможно, вышел из магазина, а это нежелательно: авторизовать надо только тех, которые прошли страницу входа loginform.html */
        header("Location: redirectfile1.php");
        exit;
    }
}

$var = new First();
$var->verFunction();
