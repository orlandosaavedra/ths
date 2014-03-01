<?php

/**
 * GtkEntry that allows only numeric input
 *
 * @author orlando
 */
class GtkEntryNumeric extends GtkEntry
{
    protected $decimal;
    protected $negative;
    
    public function __construct($max=0, $decimal='.', $negative=true)
    {
        parent::__construct(null, $max);
        $this->connect('key-press-event', array($this, 'restrictNumeric'));
        $this->decimal = $decimal;
        $this->negative = $negative;
        $this->set_alignment(1);
        if($max!=0){
            $this->set_size_request($max*15, -1);
        }
    }
    
    public function restrictNumeric($entry, $event)
    {
        switch($event->keyval){
            case Gdk::KEY_Left:
            case Gdk::KEY_Right:
            case Gdk::KEY_BackSpace:
            case Gdk::KEY_Delete:
            case Gdk::KEY_Tab:
                return false;
        }
        
        //numeric keyboard
        if (65456 <= (int)$event->keyval && (int)$event->keyval <= 65465){
            return false;
        }

        if ($this->decimal == chr($event->keyval)){ //decimal symbol
            //We should only allow decimal symbol after a number and only once
            if (!strstr($this->get_text(), $this->decimal) && strlen($this->get_text())>0){
                return false;   
            }else{
                return true;
            }
        }
        
        if ($this->negative && ('-' == chr($event->keyval))){
            if (strlen($this->get_text())===0){
                return false;
            }else{
                return true;
            }
        }
        
        // Last check for non numeric input
        if (!preg_match('/[0-9]/', chr($event->keyval))){
            return true;
        }else {
            return false;
        }
    }
    
    public function get_value()
    {
        return floatval(str_replace($this->decimal, '.', $this->get_text()));
    }
}

/** TEST **/
/*Comment this line to test
$a = new GtkWindow();
$a->connect_simple('destroy', array('gtk', 'main_quit'));
$b = new GtkEntryNumeric(5, ',',true);
$a->add($b);
$a->show_all();

$b->connect('button-release-event', 'show');

function show($b)
{
    echo $b->get_value();
}

Gtk::main();/**/