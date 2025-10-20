<?php
//========================================================================//
//	AUTOR  : JUAN CARLOS PINTO LARICO
//	FECHA  : FEBRERO  2021
//	VERSION: 1.9.0.2
//	E-MAIL : jcpintol@hotmail.com
//  hoja de seccion personalizada para su busqueda
//========================================================================//
//                        IMAGEN CAPCHA
//========================================================================//

if( isset($_GET['img']) && $_GET['img']=="true"){
    // Verificar si la extensión GD está instalada
    if (!extension_loaded('gd')) {
        die('La extensión GD no está instalada. Por favor, habilítala en php.ini');
    }

    session_start();
    $texto = randomText(4,'true');
    $captcha = imagecreate(90, 40);
    imagecolorallocate($captcha, 255, 255, 255);
    $textColor = imagecolorallocate($captcha, 193, 50, 93);
    $lineColor = imagecolorallocate($captcha, 193, 50, 93);
    $imageInfo[]=90;
    $imageInfo[]=36;
    $linesToDraw = 16;
    for($i=0; $i<$linesToDraw; $i++ ){
        $xStart = mt_rand(0, $imageInfo[0]);
        $xEnd = mt_rand(0, $imageInfo[0]);
        imageline($captcha, $xStart, 0, $xEnd, $imageInfo[1], $lineColor );
    }
    imagettftext( $captcha, 30, 2, 5, 36, $textColor, "../css/sewer.ttf", $texto );
    $_SESSION['key'] = md5($texto);
    imagesavealpha($captcha, true);
    header("Content-type: image/png;");
    imagepng($captcha);
    imagedestroy($captcha);
}

function randomText($length, $alpha){
    if($alpha!=NULL) $ap=9;
    else $ap=35;
    $lista = "0123456789abcdefghijklnmopqrstuvwxyz";
    $key = ''; // Inicializar la variable $key
    for($i=0;$i<$length;$i++) {
        $key .= $lista[rand(0,$ap)];
    }
    return $key;
}
?>