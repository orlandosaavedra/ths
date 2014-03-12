<?php

/**
 * Description of ProductCompatibilityListView
 *
 * @author orlando
 */
class ProductCompatibilityListView extends GtkTreeView
{
    const COLUMN_PRODUCT_ID = 0;
    const COLUMN_MODEL = 1;
    const COLUMN_VERSION = 2;
    const COLUMN_OTHER = 3;
    const COLUMN_YEAR_FROM = 4;
    const COLUMN_YEAR_TO = 5;
    
    /**
     *
     * @var Array
     */
    protected $columnTitles = array(
        'PID',
        'Modelo',
        'Version',
        'Otro',
        'Desde',
        'Hasta'
        
    );
    
    public function __construct()
    {
        $model = new GtkListStore(
                    GObject::TYPE_LONG, // product_id
                    GObject::TYPE_STRING, // product_compatibility.model
                    GObject::TYPE_STRING, // product_compatibility.version
                    GObject::TYPE_STRING, // product_compatbility.other
                    GObject::TYPE_STRING, // product_compatibility.year_from
                    GObject::TYPE_STRING // product_compatibility.year_to
                );
        
        parent::__construct($model);    
        $this->createColumns();
        
        $this->get_selection()->set_mode(Gtk::SELECTION_MULTIPLE);
        $this->connect('button-press-event', array($this, 'onButton'));
    }
    
    protected function createColumns()
    {
        $columnTitles = $this->columnTitles;
        
        for ($i=0;$i<count($columnTitles);++$i){
            $crt = new GtkCellRendererText();
            $col = new GtkTreeViewColumn($columnTitles[$i], $crt, 'text', $i);
            $this->append_column($col);
            
            if ($i == self::COLUMN_PRODUCT_ID){
                $col->set_visible(false);
            }
        }
    }
    
    public function onButton($view, $event)
    {
        if($event->button==3){
            $menu = new GtkMenu();
            $menuitemdelete = new GtkMenuItem('Eliminar');
            $menuitemdelete->connect_simple('activate', array($this, 'removeSelection'));
            $menu->append($menuitemdelete);
            $menu->show_all();
            $menu->popup();
        }else{
            return false;
        }
    }
    
    /**
     * 
     * @param ProductCompatibility $pc
     */
    public function append(ProductCompatibility $pc)
    {        
        $row = array(
            $pc->product_id,
            ($pc->model)?: ProductCompatibility::MATCH_ALL,
            ($pc->version)?: ProductCompatibility::MATCH_ALL,
            ($pc->other)?: ProductCompatibility::MATCH_ALL,
            ($pc->year_from)?: ProductCompatibility::MATCH_ALL,
            ($pc->year_to)?: ProductCompatibility::MATCH_ALL,
            $pc->obs
        );
        
        $model = $this->get_model();
        $model->append($row);
    }
    
    public function getCompatibilityList()
    {
        $list = array();
        $viewmodel = $this->get_model();
        $iter = $viewmodel->get_iter_first();
        
        do {
            //$row = new ProductCompatibility();
            $product_id = $viewmodel->get_value($iter, self::COLUMN_PRODUCT_ID);
            $model = $viewmodel->get_value($iter, self::COLUMN_MODEL);
            $version = $viewmodel->get_value($iter, self::COLUMN_VERSION);
            $other = $viewmodel->get_value($iter, self::COLUMN_OTHER);
            $year_from = $viewmodel->get_value($iter, self::COLUMN_YEAR_FROM);
            $year_to = $viewmodel->get_value($iter, self::COLUMN_YEAR_TO);
            $obs = $viewmodel->get_value($iter, self::COLUMN_OBS);
            
            $row = new ProductCompatibility();            
            $row->product_id = $product_id;
            $row->model = ($model!==ProductCompatibility::MATCH_ALL)? $model : null;
            $row->version = ($version!==ProductCompatibility::MATCH_ALL)? $version : null;
            $row->other = ($other!==ProductCompatibility::MATCH_ALL)? $other : null;
            $row->year_from = ($year_from!==ProductCompatibility::MATCH_ALL)? $year_from : null;
            $row->year_to = ($year_to!==ProductCompatibility::MATCH_ALL)? $year_to : null;
            $row->obs = ($obs!==ProductCompatibility::MATCH_ALL)? $obs : null;
            $list[] = $row;
            $iter = $viewmodel->iter_next($iter);
        }while($iter!==null);
        
        return $list;
    }
    
    public function removeSelection()
    {
        list ($model, $paths) = $this->get_selection()->get_selected_rows();
        $toRemove = array();
        foreach ($paths as $path){
            $toRemove[] = $model->get_iter($path);
        }
        
        foreach($toRemove as $iter){
            $model->remove($iter);
        }
    }
}
