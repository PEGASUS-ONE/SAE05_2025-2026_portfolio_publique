<?php
//Si vous ne voyez pas le captcha sur le site :
//  1. Allez dans les fichier de votre xampp et ouvrez le fichier xampp/php/php.ini
//  2. Décommentez la ligne extension=gd (Ligne 931 du fichier sur une configuration de base)
session_start();
$_SESSION['captcha'] = mt_rand(2,10);
$_SESSION['captcha2'] = mt_rand(2,10);
$img = imagecreate(100, 100);

$bg = imagecolorallocate($img, 200, 255, 255);
$textcolor = imagecolorallocate($img, 0, 0, 0);
imagestring($img, 20, 25, 50, $_SESSION['captcha']."*".$_SESSION['captcha2'], $textcolor);


header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);

