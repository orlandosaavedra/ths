<?php

/**
 * Description of SellWindow
 *
 * @author orlando
 */
class SalesWindow extends GtkWindow
{
    /**
     *
     * @var SalesCartFrame
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
        $this->set_title(__APP__ .' - Venta');
        $this->search = new ProductSearchFrame();
        $this->search->view->connect('rightclick', array($this, 'showProductDetail'));
        $this->search->connect('search', array($this, 'search'));
        $this->search->connect_simple('activated', array($this, 'addToCart'));
        
        $vbox = new GtkVbox();
        $this->add($vbox);
        
        $this->cart = new SalesCartFrame();
        $this->cart->connect('sell', array($this, 'sell'));
        $this->cart->connect('quote', array($this, 'quote'));

        $vbox->pack_start($this->search);
        $vbox->pack_start($this->cart);
    }
    
    public function addToCart()
    {
        
        $row = $this->search->getSelected();
        
        if ($row->stock == 0){
            echo 'Warning: no stock'.PHP_EOL;
        }
        
        $this->cart->append($row);
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
     * @param ProductsView $view
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
                'Confirmar',
                $this,
                Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
                    Gtk::STOCK_OK, Gtk::RESPONSE_OK));
        
        $hbox = new GtkHBox();
        $hbox->pack_start(new GtkLabel('Sucursal: '));
        $cbox = new BranchesComboBox();
        $cbox->populate();
        $hbox->pack_start($cbox);
        $diag->vbox->add($hbox);
        $diag->show_all();
        
        $response = $diag->run();
        $diag->destroy();
        
        if ($response===Gtk::RESPONSE_OK){
            $this->doSell($cbox->getSelected());
        }
    }
    
    public function doSell($branch)
    {
        $dbm = new THSModel();
        $bid = $branch->id;
        Main::debug($bid);
        $alert = array();
        
        $products = $this->cart->getProducts();
        Main::debug($products[0]->qty);
        
        foreach ($products as $product){
            if ($product->stock[$bid] < $product->qty){
                $alert[] = "{$product->id}: No existe stock suficiente en sucursal";
                
                if ($product->stock[0] < $product->qty){
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
            
            $alert[] = "¿Desea realizar la venta de todas formas?";

            $dialog->vbox->add(new GtkLabel(implode("\n", $alert)));
            $dialog->show_all();
            
            if ($dialog->run()===Gtk::RESPONSE_NO){
                $dialog->destroy();
                return;
            }
            
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
        //If cart is empty do nothing
        if ($this->cart->getRows() == null){
            return; 
        }
        
        // Show dialog to choose where to save the file
        $diag = new GtkFileChooserDialog(
                'Guardar Cotización',
                $this,
                Gtk::FILE_CHOOSER_ACTION_SAVE,
                array(Gtk::STOCK_OK, Gtk::RESPONSE_OK,
                      Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));
        
        $diag->set_current_name('cotizacion.pdf');
        $diag->set_current_folder($_SERVER['HOMEPATH']);
        $diag->set_do_overwrite_confirmation(true);
                
        if ($diag->run() == Gtk::RESPONSE_OK){
            
            $filename = $diag->get_filename();
            $diag->destroy();
            $dialog = new GtkDialog('Generando cotización', $this, Gtk::DIALOG_MODAL);
            $dialog->vbox->pack_start(new GtkLabel('Porfavor espere...'));
            $dialog->show_all();
            Main::refresh();
            sleep(1);
            DocumentFactory::generateQuote($this->cart, $filename);
            $dialog->destroy();
        }else{
            $diag->destroy();
            return;
        }        
    }
}

