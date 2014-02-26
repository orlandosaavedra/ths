<?php

/**
 * Main Window
 *
 * @author orlando
 */
class MainWindow extends GtkWindow
{
    /**
     *
     * @var gsignal
     *
    public $__gsignals = array(
        'product' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'sell' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'management' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'categories' => array()
        
    );*/
    
    public function __construct()
    {
        parent::__construct();
        $this->_build();
    }
    
    /**
     * 
     */
    private function _build()
    {
        $this->set_title('The Honda Store');
        $vbox = new GtkVBox();
        
        $mainButtons=array(
            $products = new GtkButton('Productos'),
            $sells = new GtkButton('Venta'),
            $categories = new GtkButton('Categorias/Vehiculos'),
            $management = new GtkButton('Administración'),
        );
        
        foreach ($mainButtons as $btn){
            $label = $btn->get_child();
            $label->modify_font(new PangoFontDescription('Arial 23'));
        }
        
        $sells->connect_simple('clicked', array($this, 'sells'));
        $products->connect_simple('clicked', array($this, 'products'));
        $categories->connect_simple('clicked', array($this, 'categories'));
        $management->connect_simple('clicked', array($this, 'management'));
        
        $vbox->pack_start(new GtkLabel(''), true, true, true);
        
        $hbox = new GtkHBox(true);
        $hbox->pack_start($products);
        $hbox->pack_start($sells);
        $vbox->pack_start($hbox);
        $hbox = new GtkHBox(true);
        $hbox->pack_start($categories);
        $hbox->pack_start($management);
        $vbox->pack_start($hbox); 
        $vbox->pack_start(new GtkLabel(''), true, true, true);
        
        $this->add($vbox);
    }
    
    /**
     * 
     */
    public function sells()
    {
        $sell = new SellsWindow();
        $sell->set_transient_for($this);
        $sell->show_all();
    }
    
    /**
     * 
     */
    public function products()
    {
        $income = new ProductsWindow();
        $income->set_transient_for($this);
        $income->set_modal(true);
        $income->connect('create', array($this, 'createProduct'));
        $income->connect('modify', array($this, 'searchToModify'));
        $income->connect('stock', array($this, 'productsStock'));
        //$income->connect()
        $income->show_all();
    }
    
    public function productsStock($income)
    {
        $income->destroy();
        $a = new ProductsStockWindow();
        $a->set_transient_for($this);
        $a->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $a->set_size_request(-1, 400);
        $a->show_all();
        
    }
    
    public function management()
    {
        
    }
    
    /**
     * 
     * @param type $income
     */
    public function createProduct($income)
    {
        $income->destroy();
        $create = new ProductCreateWindow();
        $create->set_transient_for($this);
        $create->set_modal(true);
        $create->show_all();
    }
    
    public function searchToModify($income)
    {
        $income->destroy();
        $window = new ProductSearchWindow();
        $window->set_transient_for($this);
        $window->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $window->set_size_request(-1, 300);
        $window->show_all();
        
        $window->searchFrame->connect('activated', array($this, 'modifyProduct'));
        /*
        $income->destroy();
        $modify = new ProductModifyWindow();
        $modify->set_transient_for($this);
        $modify->set_modal(true);
        $modify->show_all();*/
    }
    
    public function modifyProduct(ProductSearchFrame $searchFrame)
    {
        $product= $searchFrame->getSelected();
        $mwin = new ProductModifyWindow($product->id);
        $mwin->set_transient_for($searchFrame->get_toplevel());
        $mwin->set_destroy_with_parent(true);
        $mwin->show_all();
    }
    
    /**
     * @overrides show_all
     */
    public function show_all()
    {
        parent::show_all();
        $this->maximize();
    }    
    
    public function categories()
    {
        $win = new CategoriesWindow();
        $win->set_transient_for($this);
        $win->set_modal(true);
        $win->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $win->set_size_request(600, 400);
        $win->show_all();
        
    }
}

//Gobject::register_type('MainWindow');