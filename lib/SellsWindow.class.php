<?php

/**
 * Description of SellWindow
 *
 * @author orlando
 */
class SellsWindow extends GtkWindow
{
    /**
     *
     * @var ProductCartFrame
     */
    public $cart;
    /**
     *
     * @var ProductSearchFrame
     */
    public $search;
    
    public function __construct()
    {
        parent::__construct();
        $this->_build();
    }
    
    private function _build()
    {
        $this->set_position(Gtk::WIN_POS_CENTER_ON_PARENT);
        $this->set_resizable(false);
        $this->set_title('The Honda Store - Venta');
        //$this->set_keep_above(true);
        $this->set_modal(true);
        $this->search = new ProductSearchFrame();
        $this->search->view->connect('rightclick', array($this, 'showProductDetail'));
        $this->search->connect('search', array($this, 'search'));
        $this->search->connect_simple('activated', array($this, 'addToCart'));
        
        $vbox = new GtkVbox();
        $this->add($vbox);
        
        $this->cart = new ProductCartFrame();
        $this->cart = $this->cart;
        $this->cart->connect('sell', array($this, 'sell'));
        $this->cart->connect('quote', array($this, 'quote'));

        $vbox->pack_start($this->search);
        $vbox->pack_start($this->cart);
        $this->set_size_request(1024,768);
        
        $this->show_all();
    }
    
    public function addToCart()
    {
        
        $row = $this->search->getSelected();
        
        if ($row->stock == 0){
            echo 'Warning: no stock'.PHP_EOL;
        }
        
        $this->cart->append($row);
    }
    
    public function getCartItems()
    {
        
    }
    
    public function setSearchResults($data)
    {
        
    }
    
    /**
     * 
     * @param ProductSearchFrame $frame
     */
    public function search($frame)
    {
        $frame->clear();
        $dbm = new THSModel();
        
        $results = $dbm->searchProduct($frame->getSearch(), $frame->compatibility->getCompatibility());
        
        foreach($results as $pid){
            $frame->appendResult(Product::getFromId($pid));
        }
    }
    
    /**
     * 
     * @param ProductView $view
     */
    public function showProductDetail($view)
    {
        $product = $view->getSelected();
        if ($product){
            $win = new ProductModifyWindow($product->id);
            $win->lock();
            $win->set_transient_for($this);
            $win->set_modal(true);
            $win->show_all();
        }
    }
    
    public function sell()
    {
        if (count($this->cart->getRows())==0){
            return false;
        }
        
        $diag = new GtkDialog(
                'Confirma',
                $this,
                Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_YES, Gtk::RESPONSE_YES,
                    Gtk::STOCK_NO, Gtk::RESPONSE_NO));
        
        $hbox = new GtkHBox();
        $hbox->pack_start(new GtkLabel('Sucursal: '));
        $cbox = GtkComboBox::new_text();
        $dbm = new THSModel();
        $bs = $dbm->getBranches();
        
        foreach ($bs as $b){
            $cbox->append_text($b->name);
        }
        
        $cbox->set_active(0);
        $hbox->pack_start($cbox);
        $diag->vbox->add($hbox);
        $diag->show_all();
        
        switch($diag->run()){
            case Gtk::RESPONSE_YES:
                $diag->destroy();
                $this->doSell($cbox->get_active_text());
                break;
            case Gtk::RESPONSE_NO:
                $diag->destroy();
                break;
        }
    }
    
    public function doSell($branch)
    {
        $dbm = new THSModel();
        $bs = $dbm->getBranches();
        $alert = array();
        
        foreach ($bs as $b){
            if ($branch == $b->name){
                $bid = $b->id;
            }
        }
        
        $products = $this->cart->getProducts();
        
        foreach ($products as $product){
            if ($product->stock[$bid] < $product->qty){
                $alert[] = "{$product->id}: No existe stock suficiente en sucursal";
                
                if ($product->stock[0]<$product->qty){
                    $alert[] = "{$product->id}: No existe stock suficiente";
                }else{
                    $alert[] = "{$product->id}: Se utilizará stock de otra sucursal";
                }
            }
        }
        
        if (count($alert)>0){
            $dialog = new GtkDialog(
                    'Advertencia', 
                    $this,
                    Gtk::DIALOG_MODAL,
                    array(Gtk::STOCK_NO, Gtk::RESPONSE_NO,
                        Gtk::STOCK_YES, Gtk::RESPONSE_YES));

            $dialog->vbox->add(new GtkLabel(implode("\n", $alert)));
            $dialog->show_all();
            $dialog->run();
            $dialog->destroy();
            
            
        }
        
        foreach ($products as $product){
            $newstock = $product->stock[$bid]-$product->qty;
            
            if ($newstock < 0){
                foreach ($product->stock as $bb => $val){
                    $ustock = $product->stock[$bb]+$newstock;                    
                    $dbm->setProductStock($product->id, $bb, $ustock);
                }
            }
            
            $dbm->setProductStock($product->id, $bid, $newstock);
            $dbm->registerSale(THS_CURRENT_EMPLOYEE_ID, $bid, $this->cart);
        }

        $this->cart->clear();
        $this->search->clear();
        $dialog = new GtkDialog(
                    'Advertencia', 
                    $this,
                    Gtk::DIALOG_MODAL,
                    array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));

            $dialog->vbox->add(new GtkLabel('Venta realizada'));
            $dialog->show_all();
            $dialog->run();
            $dialog->destroy();
        
    }
    
    public function quote()
    {
        $diag = new GtkFileChooserDialog(
                'Guardar Cotización',
                $this,
                Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_OK, Gtk::RESPONSE_OK,
                      Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));
                
        if ($diag->run() == Gtk::RESPONSE_OK){
            $products = $this->cart->getProducts();
            $diag->destroy();
            $dialog = new GtkDialog('Generando cotización', $this, Gtk::DIALOG_MODAL);
            $dialog->vbox->pack_start(new GtkLabel('Porfavor espere...'));
            $dialog->show_all();
            Main::refresh();
            sleep(1);
            DocumentFactory::generateQuote($products, $diag->get_filename());
            $dialog->destroy();
        }else{
            $diag->destroy();
            return;
        }        
    }
}

