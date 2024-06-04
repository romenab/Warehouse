<?php

require_once "app/Tasks.php";
require_once "app/Warehouse.php";
$allProducts = new Tasks();
$show = new Warehouse($allProducts);
echo "Welcome to Warehouse!" . PHP_EOL;
$allProducts->logIn();
$show->getTable();
while (true) {
    echo "1. Add new product" . PHP_EOL .
        "2. Update product" . PHP_EOL .
        "3. Delete product" . PHP_EOL .
        "4. View changes log" . PHP_EOL .
        "5. Exit" . PHP_EOL;
    $userAction = (int)readline("Enter your action: ");
    $show->chooseAction($userAction);
}
