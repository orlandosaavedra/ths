<?php

/**
 * Description of LoginModule
 *
 * @author orlando
 */
class LoginModule 
{
    public $window;
    /**
     *
     * @var string
     */
    private $host, $dbname;
    
    public function __construct($host, $dbname)
    {
        $this->window = new LoginWindow();
        $this->host = $host;
        $this->dbname = $dbname;
    }
    
    /**
     * Starts DB login process
     * @return boolean
     */
    public function run($message=null)
    {
        $lw = $this->window;
        $lw->set_position(Gtk::WIN_POS_CENTER);
        if ($lw->run($message)){
            try{
            THSModel::connect(
                    $this->host,
                   $lw->getUsername(),
                    $lw->getPassword(),
                    $this->dbname
                );
            }  catch (Exception $e){
                
                return $this->run($e->getMessage());
            }
            
            return true;
            
        }else{
            return false;
        }
    }
}
