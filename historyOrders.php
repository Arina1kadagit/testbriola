<?php
    session_start();
    header('X-Accel-Buffering: no');
    header('Content-type: text/html; charset=utf-8');
?>
<?php require_once("includes/connection.php"); ?>

<?php

$_GET["page"] = 1;

$rowsOnPage = 10;
$index = 0;
$countPrint = 0;
$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
$endIndex = $startIndex + $rowsOnPage - 1;

//Заголовок
echo '<div class="history_header">';
echo '<div class="labelListOrder" onclick="returnToShop()">История заказов</div>';
echo '</div>';

//Контейнер
echo '<div id="history_list_orders" class="history_list_orders">';

echo '<div class="date_container">';
echo '<div class="date_container_1">';
echo '<label for="date1">С:  </label>';
echo '<input type="date" id="date1" name="date1"/>';
echo '</div>';
echo '<div class="date_container_2">';
echo '<label for="date2">По: </label>';
echo '<input type="date" id="date2" name="date2"/>';
echo '</div>';
echo '<div class="date_post">';
echo '<div class="button" onclick="printDate(this)">Поиск</div>';
echo '</div>';

echo '</div>';
                
/*
foreach ($_GET as $key => $value) {
    $_GET[$key] = $value;
    echo $key.' - '.$value;
    echo '</br>';
    echo '</br>';
}
*/

//Здесь запрос и ответ

//все параметры превратить в строку
$contractGUID = $_GET['contractGUID']; //в запрос

if ($contractGUID != ''){
    //Получаем и форматируем текущую дату
    $date2 = date("Ymd");
    $date1 = strtotime('-7 days');
    $date1 = date('Ymd', $date1);

    echo '<div class="orders_container">';

    // Составляем и отправляем запрос данных заказа для вывода (getOrder)
    // Инициализация, установка заголовков запроса и других параметров запроса
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/getHistoryOrders');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Массив передаваемых сервису параметров
    $arrayBodyRequest = [
          "contractGUID" => $contractGUID,
          "date1" => $date1,
          "date2" => $date2,
          "last"=> true,
    ];

    // Массив передаваемых параметров в JSON
    $textBodyRequest = json_encode($arrayBodyRequest);
    // Отладка
    //echo 'Запрос:<br>' . $textBodyRequest . '<br>';

    // JSON помещаем в тело запроса
    curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
    // Отправляем запрос, получаем ответ
    $textBodyResponse = curl_exec($curl);

    // Отладка
    //echo 'Ответ:<br>' . $textBodyResponse . '<br>';
    
    $message = '';
    // Если от сервера не получен ответ
    if ($textBodyResponse === false) {
        $message.= 'Ошибка curl: ' . curl_error($curl);
        echo $message;
        http_response_code(500);
        curl_close($curl);
        return;
    // Если получен любой ответ, даже с ошибкой
    } else {
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $arrayBodyResponse = json_decode($textBodyResponse, true);
        // Если код ответа от 1С - ОК, выводим строки заказа
        if ($http_code == 200) {
            
            if (gettype($arrayBodyResponse) == 'array') {
                // Отладка
                /*
                foreach ($arrayBodyResponse as $key1 => $value1) {
                    $resultTxt= 'Ключ_1: '.$key1.' Значение_1: '.gettype($value1).PHP_EOL;
                    if (gettype($value1) == 'array') {  // Это массив контрактов с числовым индексом
                        //$resultTxt.='Тип '.gettype($value1);
                        $resultTxt.='$value1 '.count($value1);
                        foreach ($value1 as $key2 => $value2) {
                            $resultTxt.=' value2: '.gettype($value2);
                            $resultTxt.= 'Ключ_2: '.$key2.' Значение_2: '.$value2.PHP_EOL;
                            if (gettype($value2) == 'array') { // Это массив с элементами: ГУИД контракта и массив заказов по этому контракту
                                foreach ($value2 as $key3 => $value3) {
                                    $resultTxt.= 'Ключ_3: '.$key3.' Значение_3: '.$value3.PHP_EOL;
                                    if (gettype($value3) == 'array') { // Это массив заказов с числовым индексом
                                        foreach ($value3 as $key4 => $value4) {
                                            $resultTxt.= 'Ключ_4: '.$key4.' Значение_4: '.$value4.PHP_EOL;
                                            if (gettype($value4) == 'array') { // Это массив с элементами: ГУИД заказа и полное наименование заказа
                                                foreach ($value4 as $key5 => $value5) {
                                                    $resultTxt.= 'Ключ_5: '.$key5.' Значение_5: '.$value5.PHP_EOL;
                                                }
                                            }
                                        }
                                    } 
                                }
                            }
                        }
                    }
                }
                echo $resultTxt;
                */

                $arrOfOrders = $arrayBodyResponse['arrayOrdersTT'];
                //echo 'count($arrOfOrders) - '.count($arrOfOrders);
                $numrows = count($arrOfOrders);

                if ($numrows > 0){

                    $maxPage = ceil($numrows/$rowsOnPage);
                    //echo 'maxPage'.$maxPage;

                    foreach ($arrOfOrders as $index => $arrOfOrder) {
                        $orderLink = $arrOfOrder['orderLink'];
                        $numberOrder = $arrOfOrder['numberOrder'];
                        $orderDate = substr($arrOfOrder['orderDate'], 0, 10); //дата без времени
                        $overallSum = $arrOfOrder['overallSum'];
                
                        // Сначала выводим строки закзов по этой торговой точке в буфер и заодно подсчитываем количество подтверждённых и количество не подтверждённых
                        $strEcho = '';
                        $arrOfGoods= $arrOfOrder['arrayOfGoods'];

                        $class = '';
                        if (!($index >= $startIndex && $index <= $endIndex)) {
                                $class = ' hide_on_page';
                            } 
                        $index++;

                        foreach ($arrOfGoods as $index2 => $arrGood) {

                            $nomName = $arrGood['nomName'];
                            $nomQuantity = $arrGood['nomQuantity'];
                            $nomMeasure = $arrGood['nomMeasure'];

                            $strEcho.= '<div class="list_row hidden_list_row'.$class.'">';
                            $strEcho.= '<div id="numberOrder" class="data">'.$numberOrder.'</div>';
                            $strEcho.= '<div id="nameNom">'.$nomName.'</div>';
                            $strEcho.= '<div id="quantityNom">'.$nomQuantity.'</div>';
                            $strEcho.= '<div id="measureNom">'.$nomMeasure.'</div>';
                            $strEcho .= '</div>';

                        }

                        echo '<div class="panel'.$class.'" onclick="toggleOrderListTT(this)">';
                        echo '<div>'.$orderDate.'</div>'; 
                        echo '<div>'.$orderLink.'</div>';
                        echo '<div id="numberOrder" class="data">'.$numberOrder.'</div>';
                        echo '<div>'.$overallSum.'</div>';
                        echo '</div>';
                        
                        // Выводим строки с заказами торговой точки, которая ранее была подготовлена
                        echo $strEcho;
                    }
                }else{
                    echo '<p id="empty_" class="no_orders_text">Нет заказов за последнюю неделю
                        </p>';
                }




            } else {
                $message.= 'Ошибка при получении данных заказов торговой точки из 1С: ' . $arrayBodyResponse['errors'];
            }
        // Если код ответа от 1С - не ОК, устанавливаем код ответа сайта таким же
        } else {
            $message.= 'Ошибка при получении данных заказа из 1С: ' . $arrayBodyResponse['errors'];
            http_response_code($http_code);
        }   
    }
    curl_close($curl);

    echo '</div>';

        // Выводим перечисление номеров страниц
    //echo '<p class="pages">Страница:';
    echo '<p id="pages"class="pages">';
    for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
        // если номер страницы соответствует текущей
        if ($_GET["page"]==$i && $maxPage > 1) {
            echo '<a style="color: black; background: linear-gradient(
                    91.65deg, #AFFC38 2.43%, #F6FD41 100%); font-weight: 600;" href="?page='.$i.'" onclick="setPage_history(\''.$i.'\')">'.$i.'</a>';
        } else {
            if ($i <= $maxPage && $maxPage > 1) {
                echo '<a href="?page='.$i.'" onclick="setPage_history(\''.$i.'\')">'.$i.'</a>';
            } else {
                echo '<a href="?page='.$i.'" style="display: none" onclick="setPage_history(\''.$i.'\')">'.$i.'</a>';
                //echo '<a href="#" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
            }
        }
    }
    echo '</p>';
} else{
    echo '<p class="no_orders_text">Список заказов пуст, так как не выбрана торговая точка.
    </p><p id="second" class="no_orders_text">
    Вернитсь на <a onclick="returnToShop()">главный экран</a> для выбора торговой точки
    </p>';
}



echo '</div>';//контейнер


//Здесь будет запрос для получения истории заказов
//Какие параметры передавать??

?>

