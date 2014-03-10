<?php
/**
 * Description of CategoriesWindow
 *
 * @author orlando
 */
class CategoriesWindow extends GtkWindow
{
    
    /**
     *
     * @var ProductCategoryComboBox
     */
    public $combo;
    
    /**
     *
     * @var ProductCategoryListView
     */
    public $view;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->set_title('Categorias');
        $this->build();
        $this->set_wmclass(__APP__, 'Categorias');
        $this->fetchCategories();
        $this->view->connect('button-press-event', array($this, 'onButton'));
    }
    
    protected function build()
    {
        $view = $this->view = new ProductCategoryListView();
        
        $dbm = THSModel::singleton();
        $categories = $dbm->getProductCategories();
        
        foreach ($categories as $cat){
            $view->append($cat);
        }
        
        $scrw = new GtkScrolledWindow();
        $scrw->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        $scrw->add($view);
        
        $vbox = new GtkVbox();
        
        $addbtn = new GtkButton('Crear Nueva');
        $addbtn->connect_simple('clicked', array($this, 'createCategory'));
        $hbox = new GtkHbox();
        $hbox->pack_start($addbtn, false, false);
        $vbox->pack_start($hbox, false, false);
        $vbox->pack_start($scrw);
        $this->add($vbox);
    }
    
    public function onButton($view, $event)
    {
        if ($event->button===3){
            $popmenu = new GtkMenu();
            $itemModify = new GtkMenuItem('Modificar');
            $itemModify->connect('activate', array($this, 'modifyCategory'));
            $itemDelete = new GtkMenuItem('Eliminar');
            $itemDelete->connect('activate', array($this, 'deleteCategory'));
            $popmenu->append($itemModify);
            $popmenu->append($itemDelete);
            $popmenu->show_all();
            $popmenu->popup();
        }else{
            return false;
        }
    }
    
    /**
     * 
     */
    public function createCategory()
    {
        $diag = new GtkDialog(
                'Crear categoria',
                $this->get_toplevel(), 
                Gtk::DIALOG_MODAL,
                array (Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
                    Gtk::STOCK_OK, Gtk::RESPONSE_OK));
        
        $entry = new GtkEntry();
        $diag->vbox->pack_start($entry);
        $diag->show_all();
        switch($diag->run()){
            case Gtk::RESPONSE_CANCEL:
                $diag->destroy();
                return;
            case Gtk::RESPONSE_OK:     
                if ($entry->get_text()==null){
                    $diag->destroy();
                    return $this->createCategory();
                }
                
                $dbm = THSModel::singleton();
                $pc = new ProductCategory();
                $pc->name = strtoupper($entry->get_text());
                $pc->id = $dbm->createProductCategory($pc->name);
                if ($pc->id){
                    $this->view->append($pc);
                }
                break;
        }
        
        $diag->destroy();
    }
    
    public function modifyCategory()
    {
        $diag = new GtkDialog(
                'Modificar categoria',
                $this->get_toplevel(), 
                Gtk::DIALOG_MODAL,
                array (Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
                    Gtk::STOCK_OK, Gtk::RESPONSE_OK));
        
        $pc = $this->view->getSelected();
        $entry = new GtkEntry();
        $entry->set_text($pc->name);
        $diag->vbox->pack_start($entry);
        $diag->show_all();
        switch($diag->run()){
            case Gtk::RESPONSE_CANCEL:
                $diag->destroy();
                return;
            case Gtk::RESPONSE_OK:     
                if ($entry->get_text()==null){
                    $diag->destroy();
                    return $this->createCategory();
                }
                
                $dbm = THSModel::singleton();
                $pc->name = strtoupper($entry->get_text());
                if($dbm->updateProductCategory($pc)){
                    $this->fetchCategories();
                }
                break;
        }
        
        $diag->destroy();
    }
    
    protected function fetchCategories()
    {
        $view = $this->view;
        $dbm = THSModel::singleton();
        $view->get_model()->clear();
        
        $categories = $dbm->getProductCategories();
        
        foreach ($categories as $pcat){
                $this->view->append($pcat);
        }
    }

    
    public function deleteCategory()
    {
        $cat = $this->view->getSelected();
        
        if ($cat === null){
            return true;
        }
        
        $diag = new GtkDialog(
                'Confirmación',
                $this->get_toplevel(),
                Gtk::DIALOG_MODAL, 
                array (Gtk::STOCK_YES, Gtk::RESPONSE_YES,
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO));
        $msg  = '¿Seguro que desea eliminar la categoria: ';
        $msg .= "({$cat->id}) {$cat->name}?".PHP_EOL;
        $msg .= 'Esto quitará la categoría de todos los productos asociados a la misma';
        
        $diag->vbox->add(new GtkLabel($msg));
        $diag->show_all();
        switch( $diag->run()){
            case Gtk::RESPONSE_YES:
                $diag->destroy();
                $dbm = THSModel::singleton();
                $dbm->removeProductCategory($cat->id);
                $this->view->removeSelected();
                break;
            case Gtk::RESPONSE_NO:
                $diag->destroy();
                break;
        }
        
    }
    
    public function editCategory()
    {
        $category = $this->view->getActive();
        
        $diag = new GtkDialog(
                'Editar categoria', 
                $this,
                Gtk::DIALOG_MODAL, 
                array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
                    Gtk::STOCK_OK, Gtk::RESPONSE_OK));
        
        $entry = new GtkEntry($category->name);
        $diag->vbox->add($entry);
        $diag->show_all();
        
        if ($diag->run() ==  Gtk::RESPONSE_OK){
            $dbm = THSModel::singleton();
            $category->name = $entry->get_text();
            $dbm->updateProductCategory($category);
            $this->combo->fetch();
        }
        
        $diag->destroy();
    }
}
