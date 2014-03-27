<?php

/**
 * Description of ProductCompatibilityFrame
 *
 * @author orlando
 */
class ProductCompatibilityFrame extends GtkFrame
{
    const MATCH_ALL = 'TODOS';
    /**
     *
     * @var ProductCompatibilityListView
     */
    protected $view;
    
    /**
     *
     * @var ProductCompatibilityFilterPanel
     */
    protected $compatibilityFilter;
    
    protected $locked = false;
    
    public function __construct($store=true)
    {
        parent::__construct('Compatibilidad');
        $this->set_border_width(3);
        $vbox = new GtkVBox;
        $this->add($vbox);
        $hbox = new GtkHBox;
        $this->panel = $hbox;
        $this->compatibilityFilter = new ProductCompatibilityNewPanel();
        $hbox->pack_start($this->compatibilityFilter);
        
        if ($store){
            $addbtn = new GtkButton('Agregar');
            $hbox->pack_start($addbtn, false, false);
            $this->createCompatibilityListView();
            $addbtn->connect_simple('clicked', array($this, 'addCompatibility'));
        }
        
        $vbox->pack_start($hbox, false, false);
    }
    
     /**
     * Creates the GtkTreeview for compatibility list view
     * @return \GtkScrolledWindow
     */
    private function createCompatibilityListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $this->view = new ProductCompatibilityListView();
        $this->view->connect_simple('right-click', array($this, 'viewMenu'));
        
        $scrwin->add($this->view);
        
        $this->get_child()->pack_start($scrwin);
    }
    
    /**
     * 
     */
    public function modifyCompatibilities()
    {
        $win = new CategoriesWindow(CategoriesWindow::VEHICLE_FRAME);
        
        $win->set_icon_from_file(THS_LOGO_PATH);
        $win->set_transient_for($this->get_toplevel());
        $win->set_modal(true);
        $win->connect_simple('destroy', array($this, 'clearFilter'));
        $win->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $win->set_default_size(400, 250);
        $win->show_all();
    }
    
    public function addCompatibility()
    {
        $pc = $this->compatibilityFilter->getActiveFilter();
        
        if ($pc===null){
            return;
        }
        
        $pc->product_id = null;
        $this->view->append($pc);
    }
    
    public function storeCompatibility(ProductCompatibility $pc)
    {
        $this->view->append($pc);
    }
    
    /**
     * 
     * @param GtkComboBox $cbox
     */
    public function clearFilter()
    {
        $this->compatibilityFilter->changed();
    }
    
    public function viewMenu()
    {
        if ($this->locked){
            return false;
        }
        
        $menu = new GtkMenu();
        $menuitemdelete = new GtkMenuItem('Eliminar');
        $menuitemdelete->connect_simple('activate', array($this, 'removeCompatibility'));
        $menu->append($menuitemdelete);
        $menu->show_all();
        $menu->popup();
    }

    
    /**
     * 
     * @param GtkTreeView $view
     */
    public function removeCompatibility()
    {
        list($model, $paths) = $this->view->get_selection()->get_selected_rows();
        $toremove = array();
        foreach ($paths as $path){
            $iter = $model->get_iter($path);
            if (is_object($iter)){
                $toremove[] = $iter;
            }else{
                return false;
            }
        }
        
        foreach ($toremove as $iter){
            $model->remove($iter);
        }
    }
    
    /**
     * 
     * @return \ProductCompatibility
     */
    public function getCompatibilityStore()
    {
        return $this->view->getCompatibilityList();
        
        $model = $this->view->get_model();
        
        $iter = $model->get_iter_first();
        
        $compatibilities = array();
        
        if ($iter === null){
            return $compatibilities;
        }
 
        do {
            $row = new ProductCompatibility();
            for ($i=0;$i<5;++$i){
                $value = $model->get_value($iter, $i);
                // IF any value is ALL then we must set it to null
                if ($value == self::MATCH_ALL){
                    $value = null;
                }
                
                switch($i){
                    case ProductCompatibilityListView::COLUMN_PRODUCT_ID:
                        $row->product_id = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_MODEL:
                        $row->model = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_VERSION:
                        $row->version = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_OTHER:
                        $row->other = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_YEAR_FROM:
                        $row->year_from = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_YEAR_TO:
                        $row->year_to = $value;
                        break;
                    case ProductCompatibilityListView::COLUMN_OBS:
                        $row->obs = $value;
                        break;
                    default:
                        break;
                }
            }
            
            $compatibilities[] = $row;
            $iter = $model->iter_next($iter);
            
        }while($iter !== null);
        
        return $compatibilities;
    }
    
    public function getCompatibility()
    {
        return $this->compatibilityFilter->getActiveFilter();        
    }
    
    public function clear()
    {
        $this->compatibilityFilter->changed();
        $this->view->get_model()->clear();
    }
    
    public function hideView()
    {
        $this->view->set_visible(false);
    }
    
    public function lock()
    {
        $this->panel->destroy();
        $this->locked = true;
    }
}
