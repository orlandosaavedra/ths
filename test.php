<?php

$data = file_get_contents('inventario.csv');

$rows = explode("\n", $data);

$mysqli = new Mysqli('localhost', 'root', 'root', 'The_Honda_Store');


foreach ($rows as $row){
    $row = str_replace('"', '', $row);
    $cell = explode(',', $row);
    
    $pn = $cell[0];
    $desc = $mysqli->escape_string(strtoupper(trim($cell[1])));
    $model = (trim($cell[2])) ?: 'NULL';
    $version = (trim($cell[3])) ?: 'NULL';
    $other = (trim($cell[4]))?: 'NULL';
    $year_from = (trim($cell[5])) ?: 'NULL';
    $year_to = (trim($cell[6]))?: 'NULL';
    $qty = (int)$cell[7];
    $cost = (int)$cell[8];
    $price = round(((int)$cost)*1.3, 0);
    $ref = $cell[9];
    
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
            
        $model = explode(';', $model);
        $version = explode(';', $version);
        $other = explode(';', $other);
        $year_from = explode(';', $year_from);
        $year_to = explode(';', $year_to);

        for ($i=0;$i<2;$i++){
            echo 'LOOP: '.$i.PHP_EOL;
            
            $m = (key_exists($i, $model)) ? "'".$model[$i]."'" : "'".$model[0]."'";
            if (empty($m)){
                $m = "'NULL'";
            }
            
            $v = (key_exists($i, $version)) ? "'".$version[$i]."'" : "'".$version[0]."'";
            if (empty($m)){
                $v = "'NULL'";
            }
            
            $o = (key_exists($i, $other)) ? "'".$other[$i]."'" : "'".$other[0]."'";
            if (empty($o)){
                $o = "'NULL'";
            }
            
            $yf = (key_exists($i, $year_from)) ? "'".$year_from[$i]."'" : "'".$year_from[0]."'";
            if (empty($yf)){
                $yf = "'NULL'";
            }
            
            $yt = (key_exists($i, $year_to)) ? "'".$year_to[$i]."'" : "'".$year_to[0]."'";
            if (empty($yt)){
                $yt = "'NULL'";
            }
            

            $sql = "INSERT INTO product_compatibility VALUES"
                    . " ($pid, $m, $v, $o, $yf, $yt)";
            
            echo $sql.PHP_EOL;
            $mysqli->query($sql);//or die($mysqli->error);
        }
            
            
    }
    
    
}
/*
$com = new COM('Wscript.shell');
$rest = $com->run('ping 127.0.0.1', 3, false);
*/


