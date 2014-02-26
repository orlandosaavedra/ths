<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CreateProductCategoryWindow
 *
 * @author orlando
 */
class CategoriesWindow extends GtkWindow
{
    const CATEGORY_FRAME=1;
    const VEHICLE_FRAME=2;
    const BOTH_FRAME=3;
    
    /**
     *
     * @var CategoriesComboBox
     */
    public $combo;
    
    /**
     *
     * @var GtkTreeView
     */
    public $view;
    
    public $show;
    
    public function __construct($show=null)
    {
        parent::__construct();
        
        switch($show){
            case self::CATEGORY_FRAME:
                $this->show = self::CATEGORY_FRAME;
                break;
            case self::VEHICLE_FRAME:
                $this->show = self::VEHICLE_FRAME;
                break;
            case self::BOTH_FRAME:
            case null:
                $this->show = self::BOTH_FRAME;
                break;
            default:
                throw Exception('wrong option');
                break;
        }
        
        $this->set_title('Categorias/Compatibilidad');
        $this->build();
    }
    
    private function build()
    {
        $vbox = new GtkVbox();
        $this->add($vbox);
        
        if ($this->show & self::CATEGORY_FRAME){
            $this->buildCategoryFrame();
        }
        
        if ($this->show & self::VEHICLE_FRAME){
            $this->buildVehicleFrame();
        }
        
    }
    
    private function buildVehicleFrame()
    {
        $frame = new GtkFrame('Vehiculos (Compatibilidad)');
        $scrwin = new GtkScrolledWindow();
        $vbox = new GtkVBox();
        $hbox = new GtkHBox();
        $frame->add($vbox);
        
        $scrwin->add($view = $this->buildVehicleView());
        $createbtn = new GtkButton('Nuevo');
        $createbtn->connect_simple('clicked', array($this, 'createVehicle'));
        $deletebtn = new GtkButton('Eliminar');
        $deletebtn->connect_simple('clicked', array($this, 'deleteVehicle'));
        $hbox->pack_start($createbtn, false, false);
        $hbox->pack_start($deletebtn, false, false);
        $vbox->pack_start($hbox, false, false);
        $vbox->pack_start($scrwin);
        $this->get_child()->pack_start($frame);
    }
    
    public function buildCategoryFrame()
    {
        $frame = new GtkFrame('Categorias');
        $combo = $this->combo = new CategoriesComboBox();
        $combo->populate();
        $hbox = new GtkHBox;
        $hbox->pack_start($combo);
        $delbtn = new GtkButton('Quitar');
        $delbtn->connect_simple('clicked', array($this, 'deleteCategory'));
        $addbtn = new GtkButton('Nueva');
        $addbtn->connect_simple('clicked', array($this, 'createCategory'));
        $hbox->pack_start($delbtn, false, false);
        $hbox->pack_start($addbtn, false, false);
        $frame->add($hbox);
        $this->get_child()->pack_start($frame, false, false);
    }
    
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
                break;
            case Gtk::RESPONSE_OK:     
                if ($entry->get_text()==null){
                    $diag->destroy();
                    return $this->createCategory();
                }
                
                $dbm = new THSModel();
                $dbm->createProductCategory($entry->get_text());
                $this->combo->populate();
                break;
        }
        
        $diag->destroy();
    }
    
    
    /**
     * Creates the new vehicle
     */
    public function createVehicle()
    {
        //Input dialog
        $dialog = new GtkDialog(
                'Crear vehiculo',
                $this, Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
                        Gtk::STOCK_OK, Gtk::RESPONSE_OK));
        
        $row = array();
        
        $row[] = array(new GtkLabel('Modelo:'), $model = new GtkEntry());
        $row[] = array(new GtkLabel('Año'), $year = GtkSpinButton::new_with_range(1900, date('Y'), 1));
        $row[] = array(
            $throughact=new GtkCheckButton('Crear para varios años'),
            $through=GtkSpinButton::new_with_range(1900, date('Y'), 1)
            );
        
        $through->set_sensitive(false);
        
        $throughact->connect('toggled', function($check, $spin){
            if ($check->get_active()){
                $spin->set_sensitive(true);
            }else{
                $spin->set_sensitive(false);
            }
        }, $through);
        
        
        
        $row[] = array(new GtkLabel('Version'), $version = new GtkEntry());
        $row[] = array(new GtkLabel('Transmisión'), $transmission = new GtkEntry());
        
        $vbox = $dialog->vbox;
        
        foreach ($row as $r){
            $hbox = new GtkHBox(true);
            $hbox->pack_start($r[0]);
            $hbox->pack_start($r[1]);
            $vbox->pack_start($hbox);
        }
        
        $dialog->show_all();
        switch($dialog->run()){
            case Gtk::RESPONSE_CANCEL:
                $dialog->destroy();
                break;
            case Gtk::RESPONSE_OK:
                $dbm = new THSModel();
                $vmodel = $model->get_text();
                $vyear = $year->get_value();
                $vversion = $version->get_text();
                $vtransmission = trim($transmission->get_text());
                
                if ($throughact->get_active()){
                    for ($i=$vyear; $i<=$through->get_value();++$i){
                        if (!$dbm->createVehicle($vmodel, $i, $vversion, $vtransmission)){
                            $dialog->destroy();
                            return $this->create();
                        }
                    }
                    
                    $dialog->destroy();
                    $this->populate();
                }else{
                    if ($dbm->createVehicle($vmodel, $vyear, $vversion, $vtransmission)){
                        $dialog->destroy();
                        $this->populate();
                    }else{
                        $dialog->destroy();
                        return $this->create();
                    }
                }
                break;
        }
        
    }
    
    private function buildVehicleView()
    {
        $this->view = $view = new GtkTreeView();
        $model = new GtkListStore(
            GObject::TYPE_LONG,
            GObject::TYPE_STRING,
            GObject::TYPE_LONG,
            GObject::TYPE_STRING,
            GObject::TYPE_STRING
        );
        
        $view->set_model($model);
        
        $head = array('Id','Modelo', 'Año', 'Versión', 'Transmisión');
        for ($i=0;$i<count($head);$i++){
            $cr = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($head[$i], $cr, 'text', $i);
            $view->append_column($col);
            $col->set_sort_column_id($i);
            
            if ($i==0){
                $col->set_visible(false);
            }
        }
        
        $this->populate();
        $view->get_selection()->set_mode(Gtk::SELECTION_MULTIPLE);
        
        return $view;
    }
    
    private function populate()
    {
        $view = $this->view;
        $model = $view->get_model();
        $model->clear();
        $dbm = new THSModel();
        $vehicles = $dbm->getVehicles();
        
        foreach ($vehicles as $vehicle){
            $model->append(
                    array($vehicle->id, $vehicle->model, $vehicle->year, $vehicle->version, $vehicle->transmission)
                    );
        }
    }
    
    public function deleteVehicle()
    {
        $dbm = new THSModel();
        list($model, $paths) = $this->view->get_selection()->get_selected_rows();
        $to_remove = array();
        foreach ($paths as $path){
            $iter = $model->get_iter($path);
            if ($dbm->removeVehicle($model->get_value($iter, 0))){
                $to_remove[] = $iter;
            }
        }
        
        foreach ($to_remove as $iter){
            $model->remove($iter);
        }
    }

    
    public function deleteCategory()
    {
        $cat = $this->combo->getActive();
        
        if ($cat === null){
            return true;
        }
        
        $diag = new GtkDialog(
                'Confirmación',
                $this->get_toplevel(),
                Gtk::DIALOG_MODAL, 
                array (Gtk::STOCK_YES, Gtk::RESPONSE_YES,
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO));
        $msg  = '¿Está seguro que desea eliminar la siguiente categoria?'.PHP_EOL;
        $msg .= "({$cat->id}) {$cat->name}".PHP_EOL;
        $msg .= 'Esto quitará la categoría de todos los productos asociados a la misma';
        
        $diag->vbox->add(new GtkLabel($msg));
        $diag->show_all();
        switch( $diag->run()){
            case Gtk::RESPONSE_YES:
                $diag->destroy();
                $dbm = new THSModel();
                $dbm->removeProductCategory($cat->id);
                $this->combo->populate();
                break;
            case Gtk::RESPONSE_NO:
                $diag->destroy();
                break;
        }
        
    }
}
