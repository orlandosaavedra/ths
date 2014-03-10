<?php

/**
 * Description of ProductCodesFrame
 *
 * @author orlando
 */
class ProductCodesFrame extends GtkFrame
{
    /**
     *
     * @var GtkVbox
     */
    public $vbox;
    
    /**
     *
     * @var GtkTreeView
     */
    protected $view;
    
    
    protected $locked=false;
    
    protected $panel;
    
    public function __construct()
    {
        parent::__construct('Codigos externos');
        $this->vbox = new GtkVbox();
        $this->add($this->vbox);
        $this->createPanel();
        $this->createView();
    }
    
    private function createPanel()
    {
        $this->panel = $panel = new GtkHBox();
        $reference = new GtkEntry();
        $code = new GtkEntry();
        $add = new GtkButton('Agregar');
        
        $add->connect_simple('clicked', array($this, 'addReference'), $reference, $code);
        
        $panel->pack_start(new GtkLabel('Referencia:'), false, false);
        $panel->pack_start($reference, false, false);
        $panel->pack_start(new GtkLabel('Codigo:'), false, false);
        $panel->pack_start($code, false, false);
        $panel->pack_start($add, false, false);
        $this->vbox->pack_start($panel, false, false);
    }
    
    private function createView()
    {
        $model = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING);
        $this->view = new GtkTreeView($model);
        $this->view->connect('button-press-event', array($this, 'onButton'));
        $columns = new ArrayObject(array('Referencia', 'Codigo'));
        
        for($i=0;$i<$columns->count();++$i){
            $crt = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($columns[$i], $crt, 'text', $i);
            $col->set_sort_column_id($i);
            $this->view->append_column($col);
        }
        
        $scroll = new GtkScrolledWindow();
        $scroll->add($this->view);
        $scroll->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        $this->vbox->pack_start($scroll);    }
    
    /**
     * 
     * @param GtkEntry $entryRef
     * @param GtkEntry $entryCode
     */
    public function addReference(GtkEntry $entryRef, GtkEntry $entryCode)
    {
        $model = $this->view->get_model();
        $reference = strtoupper(trim($entryRef->get_text()));
        $code = trim($entryCode->get_text());
        
        $model->append(array($reference, $code));
        
        $entryRef->set_text('');
        $entryCode->set_text('');
    }
    
    public function removeReference()
    {
        list($model, $iter) = $this->view->get_selection()->get_selected();
        $model->remove($iter);
    }
    
    public function onButton($view, $event)
    {
        if ($event->button==1){ return false;}
        if ($event->button==2){ return true;}
        if ($event->button==3){
            $this->showContextMenu($event);
            return false;
        }
    }
    
    public function showContextMenu(GdkEvent $event)
    {
        if($this->locked){
            return false;
        }
        
        $parray = $this->view->get_path_at_pos($event->x, $event->y);
        $path = $parray[0][0];
                
        if ($path === null){
            return; //Nothing selected==no poup
        }
        
        $menu = new GtkMenu();
        
        $menuItemRemove = new GtkMenuItem('Eliminar');
        $menuItemRemove->connect_simple('activate', array($this, 'removeReference'));
        $menu->append($menuItemRemove);
        $menu->show_all();
        $menu->popup();
    }
    
    public function clear()
    {
        $this->view->get_model()->clear();
    }
    
    public function getCodes()
    {
        $model = $this->view->get_model();
        $iter = $model->get_iter_first();
        $codes = array();
        
        while ($iter !== null){
            $code = new stdClass();
            $code->reference = $model->get_value($iter, 0);
            $code->code = $model->get_value($iter, 1);
            $codes[]=$code;
            $iter = $model->iter_next($iter);
        }
        
        return $codes;
    }
    
    public function populate($data)
    {
        $model = $this->view->get_model();
        
        foreach ($data as $row){
            $model->append(array($row->reference, $row->code));
        }
        
    }
    
    public function lock($bool=true)
    {
        $this->locked = $bool;
        $this->panel->set_visible(false);
    }
}
