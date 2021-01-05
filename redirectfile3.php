<?php

require_once "verifyauthorization.php";

echo "<html> \n <head> \n";
echo "<title> Информация о передаче товаров в корзину </title> \n <meta charset = \"utf-8\"> \n <link rel = \"stylesheet\" href = \"ourstyles.css\"> \n";  
echo "</head> \n";
echo "<body> \n";
echo "<table> \n";
echo "<tr> \n <td style = \"height: 60px\"> <strong id = \"indcolor\"> Информация о том, что товары переданы в корзину </strong> </td> \n </tr> \n";
echo "<tr> \n <td style = \"text-align: left; height: 35px\" id = \"helptext\"> Выбранные вами товары добавлены в вашу корзину.</br> </td> \n </tr> \n </table> \n";
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
