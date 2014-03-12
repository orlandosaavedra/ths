<?php
/*
$data = file_get_contents('inventario.csv');

$rows = explode("\n", $data);

$mysqli = new Mysqli('localhost', 'root', 'root', 'The_Honda_Store');


foreach ($rows as $row){
    $row = str_replace('"', '', $row);
    $cell = explode(',', $row);
    
    $pn = $cell[0];
    $desc = $mysqli->escape_string(strtoupper($cell[1]));
    $qty = $cell[3];
    $cost = (int)$cell[4];
    $price = round(((int)$cost)*1.3, 0);
    $ref = $cell[5];
    
    $state = ($ref=="USADO")? 2 : 1;
    
    $sql = "INSERT INTO `product` VALUES "
            . "(NULL, '', $state, '$desc', $cost, $price, NULL)";
    echo $sql.PHP_EOL;
    $mysqli->query($sql)  or die($mysqli->error);
    
    echo 'inserted : '.$mysqli->insert_id.PHP_EOL;

    if ($pid = $mysqli->insert_id){
        $sql = "UPDATE `product_stock` SET `stock`='$qty' WHERE `product_id`=$pid";
        $mysqli->query($sql) or die('34'.$mysqli->error);
        $sql = "INSERT INTO product_code VALUES ($pid, '$pn', '$ref')";
        $mysqli->query($sql) or die('36'.$mysqli->error);
    }
    
}*/

$com = new COM('Wscript.shell');
$rest = $com->run('ping 127.0.0.1', 3, false);



