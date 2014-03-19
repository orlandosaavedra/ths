<?php

/**
 * Description of ProductView
 *
 * @author orlando
 */
class ProductListView extends GtkTreeView
{
    protected $viewmodel = array(
        'Codigo' => GObject::TYPE_LONG,
        'Número de Parte' => GObject::TYPE_STRING,
        'Descripción' => GObject::TYPE_STRING,
        'Estado' => GObject::TYPE_STRING,
        'Procedencia' => GObject::TYPE_STRING,
        'Precio' => GObject::TYPE_STRING,
        'Stock' => GObject::TYPE_LONG
    );
    
    public $__gsignals = array(
        'right-click' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->createModel();
        $this->createColumns();
        
        $this->connect('button-release-event', array($this, 'onButton'));
    }
    
    private function createModel()
    {
        
        $model = new GtkListStore();
        $model->set_column_types($this->viewmodel);
        
        $this->set_model($model);
    }
    
    private function createColumns()
    {
        
        $columnsHeaders = array();
        foreach ($this->viewmodel as $title => $type){
            $columnsHeaders[] = $title;
        }
        
        for ($i=0;$i<count($columnsHeaders);++$i){
            $cellrenderer = new GtkCellRendererText();
            $column = new GtkTreeViewColumn($columnsHeaders[$i], $cellrenderer, 'text', $i);
            $this->append_column($column);
            $column->set_sort_column_id($i);
            
            
            switch($columnsHeaders[$i]){
                case 'Precio':
                    $cellrenderer->set_property('xalign', 1);
                    break;
                case 'Stock':
                    $cellrenderer->set_property('xalign', 0.5);
                    break;
                case 'Descripción':
                    $column->set_expand(true);
                    $column->set_min_width(200);
                    $column->set_sizing(Gtk::TREE_VIEW_COLUMN_FIXED);
                    break;
            }
        }        
    }
    
    public function onButton($view, $event)
    {
        if ($event->button == 3){
            $this->emit('right-click');
        }     
        
        return false;
    }
    
    public function getSelected()
    {
        list($model, $iter) = $this->get_selection()->get_selected();
        if ($iter == null){
            return false;
        }
        return Product::fetch($model->get_value($iter, 0));
    }
    
    public function getList()
    {
        $model = $this->get_model();
        $iter = $model->get_iter_first();
        $list = array();
        
        while ($iter !== null){
            $list[] = $model->get_value($iter, 0);
            $iter = $model->iter_next($iter);
        }
        
        return $list;
    }
}

GObject::register_type('ProductListView');