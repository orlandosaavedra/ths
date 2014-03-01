<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'GtkEntryNumeric.class.php';
/**
 * Description of GtkEntryMoney
 *
 * @author orlando
 */
class GtkEntryMoney extends GtkEntryNumeric
{
    /**
     *
     * @var string
     */
    protected $thousands;
    /**
     *
     * @var string
     */
    protected $currency;
    
    public function __construct($max=0, $currency='$', $negative=false, $decimal='.', $thousands=',')
    {
        parent::__construct($max, $decimal, $negative);
        $this->set_text($currency.'0');
        $this->connect('key-press-event', array($this, 'format'));
        $this->thousands = $thousands;
        $this->currency = $currency;
        echo $this->decimal;
    }
    
    public function format($entry, $event)
    {
        
        if ($event->keyval === Gdk::KEY_Tab){
            return false;
        }
        
        if ($event->keyval === Gdk::KEY_BackSpace){
            $current = $this->get_text();
            $new = substr($current, 0, strlen($current)-1);
            $this->set_text($new);
            $val = $this->get_value();
            $decpos = strpos($current, $this->decimal);
            $length = strlen($current);
            if ($decpos){
                $decimals = $length - $decpos;
            }else{
                $decimals = 0;
            }
            $format = number_format($val, $decimals, $this->decimal, $this->thousands);
            $this->set_text($this->currency . $format);
            return true;
        }
        
        if (chr($event->keyval)==$this->decimal){
            $this->append_text($this->decimal);
            return true;
        }
        
        $currenttext = $this->get_text();
        
        if (strpos($currenttext, $this->decimal)=== (strlen($currenttext)-1)){
            $this->append_text(chr($event->keyval));
            return true;
        }
        
        $current = $this->get_value();
        echo 'Current value is: '.$current.PHP_EOL;
        $new = $current . chr($event->keyval);
        echo 'New value is: '.$new.PHP_EOL;
        $decpos = strpos($current, $this->decimal);
        $length = strlen($current);
        if ($decpos){
            $decimals = $length - $decpos;
        }else{
            $decimals = 0;
        }
        $new = str_replace($this->decimal, '.',$new);
        echo 'Formating: '.$new.PHP_EOL;
        $format = number_format($new, $decimals, $this->decimal, $this->thousands);
        echo 'Formated: '.$format.PHP_EOL;
        $this->set_text($this->currency . $format);
        return true;
    }
    
    public function get_value()
    {
        $val = $this->get_text();
        $val = str_replace($this->currency, '', $val);//remove currency
        $val = str_replace($this->thousands, '', $val); //remove thousands sep
        $val = str_replace($this->decimal, '.', $val); //set decimal to .
        return floatval($val);
    }
}

/** TEST **/
///*Comment this line to test
$a = new GtkWindow();
$a->connect_simple('destroy', array('gtk', 'main_quit'));
$b = new GtkEntryMoney(0, '$', true, ",", ".");
$a->add($b);
$a->show_all();

$b->connect('button-release-event', 'show');

function show($b)
{
    echo $b->get_value();
}

Gtk::main();/**/