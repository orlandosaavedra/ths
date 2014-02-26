<?php

include 'SimpleHtml.class.php';

$context = array(
    'http'=>array(
        'proxy' => 'tcp://adc-proxy.oracle.com:80',
        'request_fulluri'=> true,
    )
);
$context = stream_context_create($context);
function getOptions($catcgry1=null, $catcgry2=null, $catcgry3=null, $catcgry4=null, $catcgry5=null){
    $context = array(
    'http'=>array(
        'proxy' => 'tcp://adc-proxy.oracle.com:80',
        'request_fulluri'=> true,
        )
    );
    
    $context = stream_context_create($context);
    

    $get=5;
    if ($catcgry4==null) $get=4;
    if ($catcgry3==null) $get=3;
    if ($catcgry2==null) $get=2;
    if ($catcgry1==null) $get=1;
    
    $inputstate = ($get>1)? $get -1: $get;
    
    $url = 'inputstate='.$inputstate;
    $url.= '&catcgry1='.str_replace(' ', '+', $catcgry1);
    $url.= '&catcgry2='.str_replace(' ', '+',$catcgry2);
    $url.= '&catcgry3='.str_replace(' ', '+',$catcgry3);
    $url.= '&catcgry4='.str_replace(' ', '+',$catcgry4);
    $url.= '&catcgry5='.str_replace(array(' ','&'), array('+', '%26'),$catcgry5);

    
    
    if ($catcgry5==null){
        $url = 'http://www.hondaautomotiveparts.com/auto/jsp/mws/catdisplay.jsp?'.$url;
        $web = file_get_contents(
            $url,
        false, 
        $context);
        
        echo $url.PHP_EOL;
        $sh = new SimpleHTML($web);
        
        //echo 'getting '.'catcgry'.$get.PHP_EOL;
        $select = $sh->getElementsByAttribute('name', 'catcgry'.$get);

        //echo 'found '.$select[0]->childNodes->length.' options'.PHP_EOL;
        $ret = array();
        for ($i=0;$i<$select[0]->childNodes->length;$i++){

            $val = $select[0]->childNodes->item($i)->nodeValue;
            if (strstr($val, 'Select Here')) continue;
            $ret[] = trim($val);

        }

        return $ret;
    }else{
        $url = 'http://www.hondaautomotiveparts.com/auto/jsp/mws/prddisplay.jsp?'.$url;
        $url = str_replace('inputstate=4', 'inputstate=5', $url);
        $web = file_get_contents(
            $url,
            false, 
            $context);
        
        $parts = array();

        $sh = new SimpleHTML($web);
        $img = $sh->getElementById('catimage');
        echo $url.PHP_EOL;
        $imgurl = 'http://www.hondaautomotiveparts.com/auto/jsp/mws/'.$img->getAttribute('src');
        $table = $sh->getElementsByTagName('table');
        foreach ($table as $t){
            if ($t->getAttribute('bgcolor')=='#FFFFFF'){
                $pnr = $t->getElementsByAttribute('width', '7%');
                $il = $t->getElementsByAttribute('width', '5%');
                $dr = $t->getElementsByAttribute('width', '66%');
                for ($i=1;$i<count($pnr);$i++){
                    $part = new stdClass();
                    $part->number = $pnr[$i]->innerText;
                    $part->description = $dr[$i]->innerText;
                    $part->ilustno = $il[$i]->innerText;
                    $parts[] = $part;
                }
                return array($imgurl, $parts);
            }
        }
    }
}

$mysql = new mysqli('localhost', 'root', 'root', 'THS');

$opt1 = getOptions();
foreach ($opt1 as $model){
    echo 'getting years for '.$model.PHP_EOL;
    $opt2 = getOptions($model);
    foreach ($opt2 as $year){
        //echo 'getting trim for '. $model .' '. $year.PHP_EOL;
        $opt3 = getOptions($model, $year);
        foreach ($opt3 as $trim){
            $opt4 = getOptions($model, $year, $trim);
            //echo $model . ' ' . $year .' '. $trim . PHP_EOL;
            foreach ($opt4 as $trans){
                //echo $model . ' ' . $year .' '. $trim . ' '.$trans . PHP_EOL;
                //$opt5 = getOptions($model, $year, $trim, $trans);
                
                $ctg = getOptions($model, $year, $trim, $trans);
                
                $trans_ = str_replace(array('KA', 'KL', 'KH'), '', $trans);
                $trim_ = str_replace('DR', 'P', $trim);
                $mysql->query("INSERT INTO `vehicle` VALUES (NULL,'$model', $year, '$trim_', '$trans_')");
                $vehicle_id = $mysql->insert_id;
                if ($mysql->error == null){
                
                    foreach ($ctg as $c){
                        
                        $mysql->query("INSERT INTO category VALUE (NULL, '$c')");
                        echo $mysql->error.PHP_EOL;
                        if ($mysql->error){
                            $cat_id = $mysql->query("SELECT id FROM category WHERE name='$c'")->fetch_object()->id;
                        }else{
                            $cat_id = $mysql->insert_id;
                        }
                        $r = getOptions($model, $year, $trim, $trans, $c);
                        //file_put_contents(basename($r[0]), file_get_contents($r[0], false, $context));
                        foreach ($r[1] as $part){
                            $mysql->query("INSERT INTO product VALUES(NULL, '{$part->number}', 0, '{$part->description}', 0, $cat_id)");
                            if (!$mysql->error){
                                $pid1 = $mysql->insert_id;
                                
                            }else{
                                $res = $mysql->query("SELECT id FROM product WHERE partnumber='{$part->number}' AND state=0");
                                echo $mysql->error.PHP_EOL;
                                $pid1 = $res->fetch_object()->id;
                            }
                            
                            $mysql->query("INSERT INTO compatibility VALUES ($pid1, $vehicle_id)");
                            $mysql->query("INSERT INTO product VALUES(NULL, '{$part->number}', 1, '{$part->description}', 0, $cat_id)");
                            if (!$mysql->error){
                                $pid2 = $mysql->insert_id;
                                
                            }else{
                                $res = $mysql->query("SELECT id FROM product WHERE partnumber='{$part->number}' AND state=1");
                                echo $mysql->error.PHP_EOL;
                                $pid2 = $res->fetch_object()->id;
                            }
                            
                            $mysql->query("INSERT INTO compatibility VALUES ($pid2, $vehicle_id)");


                        }
                    }
                    
                }else{
                    echo $mysql->error.PHP_EOL;
                }
            }
        }
    }
}