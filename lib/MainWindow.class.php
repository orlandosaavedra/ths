<?php

/**
 * Main Window
 *
 * @author orlando
 */
class MainWindow extends GtkWindow
{
    /**
     *
     * @var GtkNotebook
     */
    protected $notebook=null;
    
    public function __construct()
    {
        parent::__construct();
        $this->set_icon_from_file(APPLOGO);
        $this->set_title(APPNAME);
        $this->notebook = new GtkNotebook();
        $this->add($this->notebook);
    }
    
    /**
     * 
     */
    public function loadModules()
    {
        $module_list = THSConfig::get('modules');
        foreach ($module_list as $moduleName){
            require_once $moduleName.'.so.php';
            $mod = new $moduleName();
            $mod->load();
            $this->notebook->append_page($mod, new GtkLabel($mod->getTitle()));
        }
    }
}