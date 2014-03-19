<?php

/**
 * Description of GtkEntryMasked
 *
 * @author orlando
 */
class GtkEntryMasker
{    
    public static $thousands = ',';
    public static $decimal = '.';
    public static $currency = '$';
    public static $decpos = 0;
    
    protected static function controlevent(GdkEvent $event)
    {
        switch($event->keyval){
            case Gdk::KEY_BackSpace:
            case Gdk::KEY_Left:
            case Gdk::KEY_Right:
            case Gdk::KEY_Delete:
            case Gdk::KEY_Tab:
                return true;
        }
        
        return false;
    }
    
    public static function maskmoney(GtkEntry $entry, GdkEvent $event)
    {
        if (self::controlevent($event)){
            return false; //let the event continue
        }
        
        $current = $entry->get_text();
        $clean = str_replace(self::$currency, '', $current);
        $clean = str_replace(self::$thousands, '' ,$clean);
        $clean = str_replace(self::$decimal, '.', $clean);
        echo $clean;
        $entry->set_text($clean);
        
        if (self::masknumeric($entry, $event)){
            $entry->set_text($current);
            $entry->set_position(strlen($current));
        }else{
            if ($event->string == self::$decimal){
                $entry->set_text($current . self::$decimal);
                $entry->set_position(strlen($current)+1);
                return true;
            }else{
                $clean .= $event->string;
                $formated = number_format((float)$clean, self::$decpos, self::$decimal, self::$thousands);
                $entry->set_text($formated);
                $entry->set_position(strlen($formated));
                return true;
            }
        }
        
        
    }
    
    public static function masknumeric(GtkEntry $entry, GdkEvent $event)
    {
        if (self::controlevent($event)){
            return false; //let the event continue
        }
        
        $current = $entry->get_text();
        if (strpos($current, self::$decimal)){
            self::$decpos = strlen($current) - (strpos($current, self::$decimal)+1);
        }else{
            self::$decpos = 0;
        }
        
        if (self::$decimal == $event->string){
            if (self::$decpos === 0){
                return false; //if decimal, let it go
            }else{
                return true; // but there can only be 1 decimal sign
            }
        }
        
        //numeric keyboard
        if (65456 <= (int)$event->keyval && (int)$event->keyval <= 65465){
            return false;
        }
        
        if (!preg_match('/[0-9]/', chr($event->keyval))){
            return true;
        }
        
        return false;
    }
    
    public static function maskuppercase(GtkEntry $entry, GdkEvent $event)
    {

        if (self::controlevent($event)){
            return false;
        }
        
        $current = $entry->get_text();

        $uppertr = array(
            'ñ'=>'Ñ',
            'á'=>'Á',
            'é'=>'É',
            'í'=>'Í',
            'ó'=>'Ó',
            'ú'=>'Ú'
        );
        
        // Workaround for certain characters that wont trigger a keypress event
        $current = strtr($current, $uppertr);
        $entry->set_text($current);
        $entry->set_position(strlen($current));

        if (key_exists($event->string, $uppertr)){
            $entry->insert_text(strlen($current), $uppertr[$event->string]);
            $entry->set_position(strlen($current)+1);
            return true;
        }
        
        if (preg_match('/[a-z]/', $event->string)){
            $entry->insert_text(strlen($current), strtoupper($event->string));
            $entry->set_position(strlen($current)+1);
            return true;
        }
    }
    
    public static function masklowercase(GtkEntry $entry, GdkEvent $event)
    {
        if (self::controlevent($event)){
            return false;
        }
        
        $current = $entry->get_text();

        $uppertr = array(
            'Ñ'=>'ñ',
            'Á'=>'á',
            'É'=>'é',
            'Í'=>'í',
            'Ó'=>'ó',
            'Ú'=>'ú'
        );
        
        // Workaround for certain characters that wont trigger a keypress event
        $current = strtr($current, $uppertr);
        $entry->set_text($current);
        $entry->set_position(strlen($current));

        if (key_exists($event->string, $uppertr)){
            $entry->insert_text(strlen($current), $uppertr[$event->string]);
            $entry->set_position(strlen($current)+1);
            return true;
        }
        
        if (preg_match('/[A-Z]/', $event->string)){
            $entry->insert_text(strlen($current), strtolower($event->string));
            $entry->set_position(strlen($current)+1);
            return true;
        }
    }
}
