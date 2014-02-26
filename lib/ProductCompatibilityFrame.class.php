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
     * @var GtkComboBox
     */
    protected $modelcbox;
    /**
     *
     * @var GtkComboBox
     */
    protected $startyearcbox;
    /**
     *
     * @var GtkComboBox
     */
    protected $endyearcbox;
    /**
     *
     * @var GtkComboBox
     */
    protected $versioncbox;
    /**
     *
     * @var GtkComboBox
     */
    protected $transmissioncbox;
    
    public function __construct($store=true)
    {
        parent::__construct('Compatibilidad');
        $this->set_border_width(3);
        $vbox = new GtkVBox;
        $this->add($vbox);
        $hbox = new GtkHBox;
        $this->panel = $hbox;
        
        $this->modelcbox = $model = GtkComboBox::new_text();
        $this->populateModels();
        
        $this->startyearcbox = $startyear = GtkComboBox::new_text();
        $this->endyearcbox = $endyear = GtkComboBox::new_text();
        $this->versioncbox = $version = GtkComboBox::new_text();
        $this->transmissioncbox = $transmission = GtkComboBox::new_text();
        
        $model->connect(
                'changed', //signal
                array($this, 'compatModelChanged'), //callback
                $startyear, $endyear, $version, $transmission //params
                );
        
        $startyear->connect_simple(
                'changed', 
                array($this, 'compatStartYearChanged'),
                $model, $startyear, $endyear, $version, $transmission
                );
        
        $endyear->connect_simple(
                'changed',
                array($this, 'compatEndYearChanged'),
                $model, $startyear, $endyear, $version, $transmission
                );
        
        $version->connect_simple(
                'changed', 
                array($this, 'compatVersionChanged'),
                $model, $startyear, $endyear, $version, $transmission
                );
        
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
        
        $hbox->pack_start(new GtkLabel('Modelo:'));
        $hbox->pack_start($model);
        $hbox->pack_start(new GtkLabel('Desde:'));
        $hbox->pack_start($startyear);
        $hbox->pack_start(new GtkLabel('Hasta:'));
        $hbox->pack_start($endyear);
        $hbox->pack_start(new GtkLabel('Versión:'));
        $hbox->pack_start($version);
        $hbox->pack_start(new GtkLabel('Transmisión'));
        
        $hbox->pack_start($transmission);
        
        if ($store){
            $hbox->pack_start($addbtn);
            $hbox->pack_start($rmbtn);
            $hbox->pack_start($confbtn);
        }
        
        $vbox->pack_start($hbox, false, false);
        
        if ($store){
            
            $this->_createCompatibilityListView();
            $addbtn->connect_simple(
                    'clicked', array($this, 'addCompatibility'),
                    $model, $startyear,$endyear, $version, $transmission
                    );

            $rmbtn->connect_simple(
                    'clicked', array($this, 'removeCompatibility'));
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
        $win->connect_simple('destroy', array($this, 'populateModels'));
        $win->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $win->set_size_request(400, 250);
        $win->show_all();
    }
    
    public function addCompatibility()
    {
        $pc = $this->getCompatibility();
        $this->storeCompatibility($pc);
    }
    
    /**
     * 
     * @param GtkComboBox $cbox
     */
    public function populateModels()
    {
        $dbm = new THSModel;
        $vmodels = $dbm->getVehicleModels();
        $cbox = $this->modelcbox;
        $cbox->get_model()->clear();
        $cbox->append_text(self::MATCH_ALL);
        foreach ($vmodels as $vmodel){
            $cbox->append_text($vmodel);
        }
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
     * @param GtkComboBox $startyear
     * @param GtkComboBox $endyear
     * @param type $version
     * @param type $transmission
     */
    public function compatModelChanged($model, $startyear, $endyear, $version, $transmission)
    { 
        // If ALL is selected we must get all availalbe years, otherwise just to the applied model
        if ($model->get_active_text() !== self::MATCH_ALL){
            $vmodel = $model->get_active_text();
        }else{
            $vmodel = null;
        }
        
        $dbm = new THSModel();
        $years = $dbm->getVehicleModelYears($vmodel);
        $max = max($years);
        $min = min($years);
        $startyear->get_model()->clear();
        $endyear->get_model()->clear();
        $version->get_model()->clear();
        $transmission->get_model()->clear();
        
        $startyear->append_text(self::MATCH_ALL);
        foreach($years as $year){
            $startyear->append_text($year);
        }
    }
    
    /**
     * 
     * @param GtkComboBox $model
     * @param GtkComboBox $startyear
     * @param GtkComboBox $endyear
     * @param GtkComboBox $version
     * @param GtkComboBox $transmission
     * @return null
     */
    public function compatStartYearChanged($model, $startyear, $endyear, $version, $transmission)
    {
        // If ALL is selected we must get all availalbe years, otherwise just to the applied model
        if ($model->get_active_text() !== self::MATCH_ALL){
            $vmodel = $model->get_active_text();
        }else{
            $vmodel = null;
        }
        
        $dbm = new THSModel;
        $years = $dbm->getVehicleModelYears($vmodel);
        $max = max($years);
        $min = (int)$startyear->get_active_text();
        
        $endyear->get_model()->clear();
        $version->get_model()->clear();
        $transmission->get_model()->clear();
        
        if ($min === 0){
            $endyear->append_text(self::MATCH_ALL);
            return;
        }
        
        foreach($years as $year){
            if ($year<$min)continue;
            $endyear->append_text($year);
        }
        
    }
    
    /**
     * Handles population of version widget
     * @param GtkComboBox $model
     * @param GtkComboBox $startyear
     * @param GtkComboBox $endyear
     * @param GtkComboBox $version
     * @param GtkComboBox $transmission
     */
    public function compatEndYearChanged($model, $startyear, $endyear, $version, $transmission)
    {

        $dbm = new THSModel;
        $vmodel = $model->get_active_text();
        $vsyear = ($startyear->get_active_text() == self::MATCH_ALL)? null: $startyear->get_active_text();
        $veyear = ($endyear->get_active_text() == self::MATCH_ALL)? null: $endyear->get_active_text();
        
        $versions = $dbm->getVehicleModelVersions($vmodel, $vsyear, $veyear);
        
        $version->get_model()->clear();
        $transmission->get_model()->clear();
        
        $version->append_text(self::MATCH_ALL);
        foreach ($versions as $v){
            $version->append_text($v);
        }
    }
    
    public function compatVersionChanged($model, $startyear, $endyear, $version, $transmission)
    {
        $dbm = new THSModel;
        $vmodel = ($model->get_active_text() == self::MATCH_ALL)? null : $model->get_active_text();
        $vsyear = ($startyear->get_active_text() == self::MATCH_ALL)? null: $startyear->get_active_text();
        $veyear = ($endyear->get_active_text() == self::MATCH_ALL)? null: $endyear->get_active_text();
        $vversion = ($version->get_active_text() == self::MATCH_ALL)? null : $version->get_active_text();
        $transmissions = $dbm->getVehicleModelTransmissions($vmodel, $vsyear, $veyear, $vversion, $transmission);
        
        $transmission->get_model()->clear();
        
        $transmission->append_text(self::MATCH_ALL);
        foreach ($transmissions as $t){
            $transmission->append_text($t);
        }
        
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
        $compat = new ProductCompatibility();
        $mdl = $this->modelcbox->get_active_text();
        $compat->model = ($mdl == self::MATCH_ALL)? null: $mdl;
        $sy = $this->startyearcbox->get_active_text();
        $compat->startyear = ($sy == self::MATCH_ALL)? null: $sy;
        $ey = $this->endyearcbox->get_active_text();
        $compat->endyear = ($ey == self::MATCH_ALL)? null: $ey;
        $v = $this->versioncbox->get_active_text();
        $compat->version = ($v==self::MATCH_ALL)?null:$v;
        $t = $this->transmissioncbox->get_active_text();
        $compat->transmission = ($t==self::MATCH_ALL)?null:$t;
        
        return $compat;
        
    }
    
    public function clear()
    {
        $this->populateModels();
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
