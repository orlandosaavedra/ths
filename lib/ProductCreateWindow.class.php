<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IncomeCreateWindow
 *
 * @author orlando
 */
class ProductCreateWindow extends GtkWindow
{
    /**
     *
     * @var ProductGeneralFrame
     */
    public $general;
    /**
     *
     * @var ProductCategoryFrame
     */
    public $category;
    /**
     *
     * @var ProductCompatibilityFrame
     */
    public $compatibility;
    /**
     *
     * @var ProductStockFrame
     */
    public $stock;
    
    /**
     *
     * @var GtkButton
     */
    protected $createbtn;
    /**
     *
     * @var GtkButton
     */
    protected $cancelbtn;
    
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->_build();
    }
    
    /**
     * Builds GUI
     */
    private function _build()
    {
        $this->set_title('Crear Producto');
        $vbox = new GtkVBox();
        $this->add($vbox);
        
        $this->general = new ProductGeneralFrame();
        $this->category = new ProductCategoryFrame();
        $this->compatibility = new ProductCompatibilityFrame();
        $this->stock= new ProductStockFrame();
        
        $vbox->pack_start($this->general, false, false, 5);
        $vbox->pack_start($this->category, false, false, 5);
        $vbox->pack_start($this->stock, false, false, 5);
        $vbox->pack_start($this->compatibility, true, true, 5);
        
        $this->createbtn = new GtkButton('Crear');
        $this->createbtn->connect_simple('clicked', array($this, 'create'));
        $this->cancelbtn = new GtkButton('Cancelar');
        $this->cancelbtn->connect_simple('clicked', array($this, 'destroy'));
        $hbox = new GtkHBox;
        
        $hbox->pack_start($this->cancelbtn);
        $hbox->pack_start($this->createbtn);
        $vbox->pack_end($hbox, false, false);
    }
    
    public function setCategory($combo)
    {
        $this->category = $combo->get_active_text();
    }
    
    public function create()
    {
        $dbm = new THSModel;
        
        $product = $this->general->getProduct();
        $product->category = $this->category->getSelectedCategory();
        $id = $dbm->createProduct($product);
        
        if (!$id){
            return false;
        }
        
        $stock = $this->stock->getStock();
        
        foreach ($stock as $branch_id => $stock){
            $dbm->setProductStock($id, $branch_id, $stock);
        }
        
        $compatibilities = $this->compatibility->getCompatibilityStore();
        
        foreach ($compatibilities as $compatibility){
            $dbm->setProductCompatibility($id, $compatibility);
        }
        
        $diag = new GtkDialog(
                'Correcto',
                $this,
                Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));

        $diag->vbox->add(new GtkLabel('Producto creado con id : '.$id));
        $diag->show_all();
        $diag->run();
        
        $this->general->clear();
        $this->stock->clear();
        $this->compatibility->clear();
        $this->category->populate();
        
        $diag->destroy();
    }
}