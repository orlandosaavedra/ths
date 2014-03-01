<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchWindow
 *
 * @author orlando
 */
final class ProductSearchFrame extends GtkFrame
{
    /**
     *
     * @var ProductsView
     */
    public $view;
    
    private $_scrwin;
    /**
     *
     * @var GtkEntry
     */
    public $searchEntry;
    /**
     *
     * @var GtkButton
     */
    public $searchButton;
    /**
     *
     * @var GtkComboBox
     */
    public $modelCombo;
    
    /**
     *
     * @var ProductCompatibilityFrame
     */
    public $compatibility;
    
    public $__gsignals = array(
        'search' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'activated' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
        
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->_build();
        $this->_connect();
    }
    
    private function _build()
    {
        $this->_buildEntryButtons();
        
        $this->compatibility = new ProductCompatibilityFrame(false);
        
        $this->_buildListView();
        $this->_buildLayout();
        
    }
    
    /**
     * Returns currently selected product (row);
     * @return \Product
     */
    public function getSelected()
    {
        list ($model, $iter) = $this->view->get_selection()->get_selected();
        echo 'VALUE: ' .$model->get_value($iter, 0).PHP_EOL;
        $row = Product::getFromId($model->get_value($iter, 0));
        return $row;
    }
    
    private function _connect()
    {
        $this->view->connect_simple('row-activated', array($this, 'emit'), 'activated');
        $this->searchButton->connect_simple('clicked', array($this, 'emit'), 'search');
    }
    
    private function _buildLayout()
    {
        $hbox=new GtkHBox;
        $vbox=new GtkVBox;
        
        $this->add($vbox);
        
        $hbox->pack_start($this->searchEntry);
        $hbox->pack_start($this->searchButton, false, false, false  );
        
        $vbox->pack_start($hbox, false, false, false);
        $vbox->pack_start($this->compatibility, false, false);
        $vbox->pack_start($this->_scrwin);

    }
    
    private function _buildEntryButtons()
    {
        $this->searchEntry = new GtkEntry();
        $this->searchButton = new GtkButton('Buscar');
        $this->searchEntry->connect_simple('activate', array($this->searchButton, 'clicked'));
        
    }
    
    
    public function setYear($combo)
    {
        $this->yearCombo->get_model()->clear();
        $dbl = new THSModel();
        $result = $dbl->query ("SELECT DISTINCT `year` FROM `vehicle` WHERE "
                . "`model`='{$combo->get_active_text()}' ORDER BY `year` ASC");
        echo $dbl->error;
        
        if ($result->num_rows){
            $this->yearCombo->append_text('');
        }
        
        while($obj = $result->fetch_object()){
            $this->yearCombo->append_text($obj->year);
        }
        
        
    }
    
    private function _buildListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $this->view = new ProductsView();
        $scrwin->add($this->view);
        
        $this->_scrwin = $scrwin;
    }
    
    /**
     * 
     * @return string
     */
    public function getSearch()
    {   
        return $this->searchEntry->get_text();
    }   
    
    public function appendResult(Product $product)
    {
        $dbm = new THSModel();
        //$stock = $dbm->getProductStock($product->id);
        $model = $this->view->get_model();
        $data = array(
            $product->id,
            $product->partnumber,
            $product->description,
            ($product->state==Product::STATE_NEW)? 'Nuevo': 'Usado',
            $product->price,
            $product->stock[0]
                );
        if (is_object($model)){
            $model->append($data);
        }else{
            return;
        }
    }
    
    public function clear()
    {
        $this->view->get_model()->clear();
    }
    
    public function getResults()
    {
        $model = $this->view->get_model();
        $iter = $model->get_iter_first();
        $ret = array();
        
        do {
            for ($i=0;$i<$model->get_n_columns();$i++){
                $product = Product::getFromId($model->get_value($iter, 0));
            }
            
            $ret[] = $product;
            $iter = $model->iter_next($iter);
        }while($iter !== null);
        
        return $ret;
    }
}

GObject::register_type('ProductSearchFrame');