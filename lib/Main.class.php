<?php

/**
 * Description of Main Class
 *
 * @author orlando
 */
class Main
{  
    /**
     * Main function
     */
    public function __construct()
    {    
        if ($this->login() === true){
            $win = new MainWindow();
            $win->connect('delete-event', array($this, 'close'));
            $win->show_all();

            Gtk::main();
        }        
    }
    
    public static function debug($var)
    {
        $debug = '[DEBUG]: '.date('Y-m-d H:i:s') . ' ' .gettype($var).':'.print_r($var, true).PHP_EOL;
        print($debug);
    }
    
    /**
     * Handles application login
     * @return boolean
     */
    public function login()
    {
        try{
            $dbm = new THSModel();
        }catch(Exception $e){
            $diag = new GtkDialog(
                    'Error',
                    null,
                    Gtk::DIALOG_MODAL,
                    array(Gtk::STOCK_OK, Gtk::RESPONSE_OK)
                    );

            $diag->vbox->pack_start(new GtkLabel("No se pudo conectar a la base de datos"));
            $diag->show_all();
            $diag->run();
            exit(1);
        }
        
        $login = new LoginWindow();
        $login->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
        
        while ($login->run() == Gtk::RESPONSE_OK){
            
            $login->setMessage('Verificando');

            while (Gtk::events_pending()) { Gtk::main_iteration(); }
            sleep(1);

            $eid = $dbm->employeeLogin($login->getUsername(), $login->getPassword());

            if ($eid){
                $employee = $dbm->getEmployee($eid);
                $login->setMessage("Bienvenido: {$employee->name} {$employee->lastname}");
                While (Gtk::events_pending()) { Gtk::main_iteration();}
                sleep(1);
                $login->destroy();
                define('THS_CURRENT_EMPLOYEE_ID', $eid);
                return true;
            }else{
                $login->setWarning('Accesso Denegado');

                while (Gtk::events_pending()) { Gtk::main_iteration(); }
                sleep(1);
                $login->destroy();
                return $this->login();
            }
            
            break;
        }
    }
    
    /**
     * Closes the application
     * @param MainWindow $window
     * @return boolean
     */
    public function close($window)
    {
        $dialog = new GtkDialog(
                'Confirmación',
                $window,
                Gtk::DIALOG_MODAL,
                array(
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                    Gtk::STOCK_YES, Gtk::RESPONSE_YES
                        )
                );
        
        $dialog->vbox->add(new GtkLabel('¿Seguro desea salir de la aplicación'));
        
        $dialog->set_size_request(400,100);
        $dialog->show_all();
        
        switch($dialog->run()){
            case Gtk::RESPONSE_YES:
                Gtk::main_quit();
                exit(0);
                break;
            case Gtk::RESPONSE_NO:
                $dialog->destroy();
                return true;
                break;
        }
            
    }
    
    /**
     * Restricts GtkEntry $entry to only accept numbers
     * @param GtkEntry $entry
     */
    public static function restrictNumbersOnly(GtkEntry $entry, GdkEvent $event)
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

        if (!preg_match('/[0-9]/', chr($event->keyval))){
            return true;
        }else{
            return false;
        }
    }
    
    public static function refresh()
    {
        while(Gtk::events_pending()){ Gtk::main_iteration(); }
    }
    
}
