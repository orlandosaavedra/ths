<?php


/**
 * Description of LoginWindow
 *
 * @author orlando
 */
class LoginWindow extends GtkWindow
{
    /**
     *
     * @var GtkEntry
     */
    protected $username;
    
    /**
     *
     * @var GtkEntry
     */
    protected $password;
    
    /**
     * @var GtkLabel
     */
    protected $warning;
    
    /**
     *
     * @var bool
     */
    protected $stop = false;
    
    public function __construct() {
        parent::__construct();
        
        $this->_build();
    }
    
    /**
     * 
     * @return String
     */
    public function getUsername()
    {
        return $this->username->get_text();
    }
    
    /**
     * 
     * @return String
     */
    public function getPassword()
    {
        return $this->password->get_text();
    }
    
    /**
     * 
     * @param string $message
     */
    public function setWarning($message)
    {
        if ($message !== null){
            $this->warning->set_markup('<span color="red">'.$message.'</span>');
            $this->warning->set_visible(true);
        }else{
            $this->warning->set_text('');
            $this->warning->set_visible(false);
        }
    }
    
    public function setMessage($message)
    {
        if ($message !== null){
            $this->warning->set_markup($message);
            $this->warning->set_visible(true);
        }else{
            $this->warning->set_text('');
            $this->warning->set_visible(false);
        }
    }
    
    public function run()
    {
        $this->stop = false;
        
        $this->username->set_sensitive(true);
        $this->password->set_sensitive(true);
        $this->show_all();
        
        while ($this->stop === false){
            while (Gtk::events_pending()){ Gtk::main_iteration(); }
        }
        
        return $this->response;
    }
    
    /**
     * Builds GUI
     */
    private function _build()
    {
        $this->username = new GtkEntry();
        $this->password = new GtkEntry();
        $this->warning = new GtkLabel();
        $this->password->set_visibility(false);
        
        $vbox = new GtkVbox();
        $this->add($vbox);
        
        $hbox = new GtkHbox();
        
        $vbox->pack_start($hbox);
        $label = new GtkLabel('Usuario :');
        $label->set_alignment(1,0.5);
        $hbox->pack_start($label);
        $hbox->pack_end($this->username, false, false);
        
        $hbox = new GtkHbox();
        $label = new GtkLabel('Password :');
        $label->set_alignment(1,0.5);
        $hbox->pack_start($label);
        $hbox->pack_end($this->password);
        $vbox->pack_start($hbox);
        $vbox->pack_start($this->warning);
        $this->warning->set_visible(false);
        
        $okbtn = new GtkButton('Ingresar');
        $vbox->pack_start($okbtn);
        
        $okbtn->connect_simple('clicked', array($this, '_onLogin'));
        $this->password->connect_simple('activate', array($okbtn, 'clicked'));
        $this->connect('delete-event', array($this, '_onDelete'));
    }
    
    public function _onLogin()
    {
        $this->stop=true;
        $this->response = Gtk::RESPONSE_OK;
        $this->username->set_sensitive(false);
        $this->password->set_sensitive(false);
    }
    
    public function _onDelete()
    {
        $this->stop = true;
        $this->response = Gtk::RESPONSE_CANCEL;
        return true;
    }
}