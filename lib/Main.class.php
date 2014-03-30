<?php

/**
 * Description of Main Class
 *
 * @author orlando
 */
class Main
{  
    
    const VERSION = 0.97;
    /**
     * 
     * @param type $var
     */
    public static function debug($var)
    {
        $backtrace = debug_backtrace();
        $debug = '[DEBUG]: '.date('Y-m-d H:i:s');
        $debug .= ' '. $backtrace[0]['file'] .':'.$backtrace[0]['line'];
        $debug .= ' '.gettype($var).':'.print_r($var, true).PHP_EOL;
        print($debug);
    }
    
    /**
     * Main function
     */
    public function __construct()
    {   
        if (strstr(strtolower(PHP_OS), 'win')){
            $this->update();
        }
        
        if ($this->login() === true){
            $win = new MainWindow();
            $win->loadModules();
            $win->connect('delete-event', array($this, 'close'));
            $win->show_all();

            Gtk::main();
        }        
    }
    
    /**
     * Checks for updates
     */
    public function update()
    {
        $dialog = new GtkMessageDialog(null, 0, Gtk::MESSAGE_INFO, 0, 'Verificando actualizaciones');
        $dialog->set_icon_from_file(THS_LOGO_FILENAME);
        $dialog->show_all();
        
        Main::refresh();
        
        $host = $GLOBALS['config']['host'];
        $check = 'http://'.$host.'/ths';
        $download = 'http://'.$host.'/ths/setup.exe';
        $obj = json_decode(file_get_contents($check));
        
        if ($obj->version > self::VERSION){
            $data = file_get_contents($download);
            $filename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'setup-ths.exe';
            file_put_contents($filename, $data);
            
            $desc  = array(
                array('pipe', 'r'),
                array('pipe', 'w'),
                array('pipe', 'a')
            );
            
            $pipes = null;
            $psid = proc_open("$filename /SILENT", $desc, $pipes);
            
            while (true){
                $stat  =  proc_get_status($psid);
                if (!$stat['running']){
                    proc_close($psid);
                    $wsh = new COM('Wscript.shell');
                    $wsh->run(APPDIR.DIRECTORY_SEPARATOR.'run.phpg', 1);
                    //pclose(popen('start /b "'.__APPDIR__.DIRECTORY_SEPARATOR.'run.phpg"',"r"));
                    self::terminate();
                }
            }
            
            
        }
        
        $dialog->destroy();
    }
    
    
    
    /**
     * Handles application login
     * @return boolean
     */
    public function login()
    {
        try{
            $dbm = THSModel::singleton();
        }catch(Exception $e){
            Main::handleException($e);
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

                Main::refresh();
                sleep(1);
                $login->destroy();
                return $this->login();
            }
            
            break;
        }
    }
    
    /**
     * Asks if user wants to close application and close it.
     * @param MainWindow $window
     * @return boolean
     */
    public function close(GtkWindow $window)
    {
        $dialog = new GtkMessageDialog(
                $window,
                0,
                Gtk::MESSAGE_QUESTION,
                Gtk::BUTTONS_YES_NO,
                '¿Seguro desea salir de la aplicación');
        
        $dialog->show_all();

        switch($dialog->run()){
            case Gtk::RESPONSE_YES:
                self::terminate();
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
    
    public function handleException(Exception $e)
    {
        $diag = new GtkMessageDialog(
                null,
                0,
                Gtk::MESSAGE_ERROR,
                Gtk::BUTTONS_OK,
                $e->getMessage());
        
        $diag->run();
        $diag->destroy();
        self::terminate();
    }
    
    public static function terminate()
    {
        Gtk::main_quit();
        exit();
    }
    
}
