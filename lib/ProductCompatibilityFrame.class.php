<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
     * @var GtkTreeView
     */
    protected $view;
    
    /**
     *
     * @var CompatibilityFilterHBox
     */
    protected $compatibilityFilter;
    
    public function __construct($store=true)
    {
        parent::__construct('Compatibilidad');
        $this->set_border_width(3);
        $vbox = new GtkVBox;
        $this->add($vbox);
        $hbox = new GtkHBox;
        $this->panel = $hbox;
        $this->compatibilityFilter = new CompatibilityFilterHBox();
        $hbox->pack_start($this->compatibilityFilter);
        
        if ($store){
            $addbtn = new GtkButton('Agregar');
            $rmbtn = new GtkButton('Quitar');
            $confbtn = new GtkButton('');
            $image = GtkImage::new_from_icon_name(Gtk::STOCK_PREFERENCES, Gtk::ICON_SIZE_BUTTON);
            $label = $confbtn->get_child();
            $label->destroy();
            $confbtn->add($image);
            $confbtn->connect_simple('clicked', array($this, 'modifyCompatibilities'));
        }

        if ($store){
            $hbox->pack_start($addbtn);
            $hbox->pack_start($rmbtn);
            $hbox->pack_start($confbtn);
        }
        
        $vbox->pack_start($hbox, false, false);
        
        if ($store){
            
            $this->_createCompatibilityListView();
            $addbtn->connect_simple('clicked', array($this, 'addCompatibility'));

            $rmbtn->connect_simple('clicked', array($this, 'removeCompatibility'));
        }
    }
    
    /**
     * 
     */
    public function modifyCompatibilities()
    {
        $win = new CategoriesWindow(CategoriesWindow::VEHICLE_FRAME);
        $win->set_transient_for($this->get_toplevel());
        $win->set_modal(true);
        $win->connect_simple('destroy', array($this, 'clearFilter'));
        $win->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $win->set_size_request(400, 250);
        $win->show_all();
    }
    
    public function addCompatibility()
    {
        $pc = $this->compatibilityFilter->getActiveCompatibility();
        $this->storeCompatibility($pc);
    }
    
    /**
     * 
     * @param GtkComboBox $cbox
     */
    public function clearFilter()
    {
        $this->compatibilityFilter->changed();
    }
    
   /**
     * Creates the GtkTreeview for compatibility list view
     * @return \GtkScrolledWindow
     */
    private function _createCompatibilityListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $model = new GtkListStore(
                GObject::TYPE_STRING, //Modelo
                GObject::TYPE_STRING, //Desde
                GObject::TYPE_STRING, //Hasta
                Gobject::TYPE_STRING, //Version
                GObject::TYPE_STRING //Transmision
        );
        
        $this->view = new GtkTreeView($model);
        $colheaders = array ('Modelo', 'Desde', 'Hasta', 'Versión', 'Transmision');
        for ($i=0; $i<count($colheaders);$i++){
            $crt = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($colheaders[$i], $crt, 'text', $i);
            $this->view->append_column($col);
            $col->set_sort_column_id($i);
        }
        
        $scrwin->add($this->view);
        
        $this->get_child()->pack_start($scrwin);
    }

    /**
     * 
     * @param GtkComboBox $model
     * @param GtkComboBox $start_year
     * @param GtkComboBox $end_year
     * @param GtkComboBox $version
     * @param GtkComboBox $transmission
     */
    public function storeCompatibility(ProductCompatibility $pc) //$model, $startyear, $endyear, $version, $transmission)
    {
        $viewmodel = $this->view->get_model();
        
        $viewmodel->append(
                array(
                    $pc->model,//->get_active_text(),
                    $pc->startyear,//->get_active_text(),
                    $pc->endyear,//->get_active_text(),
                    $pc->version,//->get_active_text(),
                    $pc->transmission//->get_active_text())
                ));
    }
    
    /**
     * 
     * @param GtkTreeView $view
     */
    public function removeCompatibility()
    {
        list($model, $iter) = $this->view->get_selection()->get_selected();
        
        if (is_object($iter)){
            $model->remove($iter);
        }else{
            return false;
        }
    }
    
    /**
     * 
     * @return \ProductCompatibility
     */
    public function getCompatibilityStore()
    {
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
                    case 0:
                        $row->model = $value;
                        break;
                    case 1:
                        $row->startyear = $value;
                        break;
                    case 2:
                        $row->endyear = $value;
                        break;
                    case 3: 
                        $row->version = $value;
                        break;
                    case 4:
                        $row->transmission = $value;
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
        return $this->compatibilityFilter->getActiveCompatibility();        
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
    }
}
