<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductView
 *
 * @author orlando
 */
class ProductListView extends GtkTreeView
{
    
    public $__gsignals = array(
        'right-click' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->_createModel();
        $this->_createColumns();
        
        $this->connect('button-release-event', array($this, 'onButton'));
    }
    
    private function _createModel()
    {
        
        $model = new GtkListStore(
                Gobject::TYPE_LONG, //id
                GObject::TYPE_STRING, //part number
                GObject::TYPE_STRING, //descripcion
                GObject::TYPE_STRING, //estado
                GObject::TYPE_LONG, //precio
                Gobject::TYPE_LONG //stock
        );
        
        $this->set_model($model);
    }
    
    private function _createColumns()
    {
        $columnsHeaders = array(
            'Codigo',
            'Número de Parte',
            'Descripción',
            'Estado',
            'Precio',
            'Stock'
        );
        
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
        return Product::getFromId($model->get_value($iter, 0));
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