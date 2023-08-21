<?php require_once("includes/connection.php"); ?>
<?php
    if ( isset( $_GET['guid'] ) ) {
        $mysqlQText = "SELECT * FROM nomenclatures WHERE guid = '".$_GET['guid']."'";
        $mysqlQuery = mysqli_query($con, $mysqlQText);
        $mysqlErrNo = mysqli_errno($con);
        $mysqlError = mysqli_error($con);
        if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной страницы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
//      echo $mysqlQText;

        if ( mysqli_num_rows( $mysqlQuery ) == 1 ) {
            
            $row = mysqli_fetch_array($mysqlQuery);

            if ( isset( $_GET['fullSize'] ) ) {
                if ($_GET['fullSize'] == true) {
                    // Отсылаем браузеру заголовок, сообщающий о том, что сейчас будет передаваться файл изображения
                    header("Content-type: image/*");
                    // И передаем сам файл
                    echo $row['picture'];
                }
            }  else {
                // Decode image from base64
                //$image=base64_decode($row['picture']);
                $image=$row['picture'];

                // Create Imagick object
                $im = new Imagick();

                // Convert image into Imagick
                $im->readimageblob($image);

                // Create thumbnail max of 200x82
                $im->thumbnailImage(160, 0, false);

                // Add a subtle border
                /*$color=new ImagickPixel();
                $color->setColor("rgb(120,120,120)");
                $im->borderImage($color,1,1);*/

                // Output the image
                $output = $im->getimageblob();
                $outputtype = $im->getFormat();

                header("Content-type: " . $outputtype);

                echo $output;
            }

        }
    }
 ?>