<?php

/**
 * Description of VehiclesView
 *
 * @author orlando
 */
class VehicleListView extends GtkTreeView
{
    const COLUMN_ID = 0;
    const COLUMN_MODEL = 1;
    const COLUMN_VERSION = 2;
    const COLUMN_OBS = 3;
    /**
     *
     * @var array 
     */
    protected $columnTitles = array('Id', 'Modelo', 'Versión', 'Otros');
    
    public function __construct()
    {
        parent::__construct();
        
        $model = new GtkListStore(
                    GObject::TYPE_LONG, //Vehicle id
                    GObject::TYPE_STRING, //Vehicle Model
                    GObject::TYPE_STRING, //Vehicle Version
                    GObject::TYPE_STRING //Vehicle Obs
                );
        
        $this->set_model($model);
        $this->createColumns();
        $this->connect('button-press-event', array($this, 'onButton'));
    }
    
    public function onButton($v, $event)
    {
        switch($event->button){
            case 1: //left click
                return false;
                break;
            case 2: //middle
                return true;
                break;
            case 3: //right click
                $this->popup();
                break;
        }
    }
    
    public function popup()
    {
        $menu = new GtkMenu();
        $menuItemEdit = new GtkMenuItem('Editar');
        $menuItemEdit->connect_simple('activate', array($this, 'editSelectedRow'));
        $menuItemRemove = new GtkMenuItem('Eliminar');
        $menuItemRemove->connect_simple('activate', array($this, 'removeSelectedRow'));
        $menu->append($menuItemEdit);
        $menu->append($menuItemRemove);
        $menu->show_all();
        $menu->popup();
        
    }
    
    public function editSelectedRow()
    {
        
    }        
    
    public function removeSelectedRow()
    {
        list ($model, $iter) = $this->get_selection()->get_selected();
        
        if ($iter){
            $dbm = THSModel::singleton();
            $dbm->removeVehicle($model->get_value($iter, self::COLUMN_ID));
            $model->remove($iter);
        }
    }
    
    /**
     * 
     */
    protected function createColumns()
    {
        $columnTitles = $this->columnTitles;
        
        for($i=0;$i<count($columnTitles);++$i){
            $crt = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($columnTitles[$i], $crt, 'text', $i);
            if ($i==0){
                // Don't show vehicle ID
                $col->set_visible(false);
            }
            $col->set_sort_column_id($i);
            $this->append_column($col);
        }
    }
    
    /**
     * 
     * @param Vehicle $v
     */
    public function appendVehicle(Vehicle $v)
    {
        $model = $this->get_model();
        
        $row = array(
            $v->id,
            ($v->model)?: Vehicle::MATCH_ALL,
            ($v->version)?: Vehicle::MATCH_ALL,
            $v->other);
        
        $model->append($row);
    }
    
    /**
     * 
     * @param type $id
     */
    public function removeVehicle($vid)
    {
        $model = $this->get_model();
        $iter = $model->get_iter_first();
        
        // Loop through all rows
        do{
            if ($iter !== null){
                $id = $model->get_value($iter, self::COLUMN_ID);
                if ($id == $vid){
                    $dbm = THSModel::singleton();
                    $dbm->removeVehicle($vid);
                    $model->remove($iter);
                    return;
                }
            }
            
            $iter = $model->iter_next($iter);
        }while($iter !== null);
    }
}
