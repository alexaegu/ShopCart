<!DOCTYPE html>
<html>
  <head>
    <title>Форма введения данных оплаты</title>
    <meta charset = "utf-8">
    <link rel = "stylesheet" href = "ourstyles.css">
  </head>
  <body>
    <?php

    require_once "verifyauthorization.php";

    echo "<form action = \"file5.php\" method = \"post\"> \n";
        echo "<table id = \"indtable\"> \n";
        echo "<tr> \n";
            echo "<td style = \"height: 60px\"> <strong id = \"indcolor\"> Форма ввода данных для оплаты </strong> </td> \n";
        echo "</tr> \n";
  
        echo "<tr> \n";
            echo "<td style = \"text-align: left; height: 35px\" id = \"helptext\"> Введите адрес доставки товаров: </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td> <input style = \"width: 97%\" type = \"text\" name = \"address1\" placeholder = \"Адрес доставки\" required> </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td style = \"text-align: left; height: 35px\" id = \"helptext\"> Введите номер вашей банковской карты (16 цифр без пробелов и разделителей): </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td> <input style = \"width: 97%\" type = \"text\" name = \"cardnumber1\" placeholder = \"Номер карты\" required> </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td style = \"text-align: left; height: 35px\" id = \"helptext\"> Введите срок действия вашей банковской карты (4 цифры без пробелов и разделителей в формате \"ммгг\"): </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td> <input style = \"width: 97%\" type = \"text\" name = \"cardvalid1\" placeholder = \"Срок действия карты\" required> </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td style = \"text-align: left; height: 35px\" id = \"helptext\"> Введите CVV-код вашей банковской карты, указанный на её обратной стороне (3 цифры без пробелов и разделителей): </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td> <input style = \"width: 97%\" type = \"text\" name = \"cardcvv1\" placeholder = \"CVV-код\" required> </td> \n";
        echo "</tr> \n";
  
  
        echo "<tr> \n";
            echo "<td> <p> <strong id = \"helptext\"> Перед нажатием кнопки \"Оплатить\" внимательно проверьте введённые данные! </strong> </p> </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
            echo "<td id = \"helptext\"> Все поля обязательны для заполнения. </td> \n";
        echo "</tr> \n";
        echo "<tr> \n";
           echo "<td style = \"height: 60px; transform: translateY(10px)\"> <input type = \"submit\" autofocus = \"true\" value = \"Оплатить\" id = \"indcolor\"> </td> \n";
        echo "</tr> \n";
        echo "</table> \n";
    echo "</form> \n";

    echo "<form action = \"goodbye.php\" method = \"post\"> \n";
        echo "<table> \n";
        echo "<tr> \n <td style = \"height: 30px; transform: translateY(10px)\"> <input type = \"submit\" value = \"Выход из магазина\" id = \"indcolor\"> </td> \n </tr> \n";
        echo "</table> \n";
    echo "</form> \n";
    ?>
  </body>
</html>
