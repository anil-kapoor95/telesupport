<?php
$cart = $controller->cart->getAll();
$cart_arr = $tpl['cart_arr'];
include PJ_VIEWS_PATH . 'pjFrontPublic/elements/car_layout2.php';
?>