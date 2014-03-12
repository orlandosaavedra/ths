<?php

/**
 * Description of ProductsStockWindow
 *
 * @author orlando
 */
class ProductsStockWindow extends GtkWindow
{
    public $productview;
    
    public function __construct()
    {
        parent::__construct();
        $this->set_title('Stock de productos');
        $this->_build();
    }
    
    private function _build()
    {
        //Main vbox
        $vbox = new GtkVbox();
        $this->add($vbox);
        
        $dbm = THSModel::singleton();
        //Filter tools
        $filterpanel = new GtkHBox;
        
        //Combobox to select branches from
        $liststore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
        $bcombo = new BranchesComboBox();
        $bcombo->populate();
        $bcombo->connect('changed', array($this, 'populate'));
        $filterpanel->pack_start(new GtkLabel('Sucursal:'), false, false);
        $filterpanel->pack_start($bcombo, false, false);
        /*
        $getbtn = new GtkButton('Obtener');
        $getbtn->connect_simple('clicked', array($this, 'populate'), $bcombo);
        $filterpanel->pack_start($getbtn, false, false);
        /**/
        $exportbtn = new GtkButton('Exportar');
        $exportbtn->connect_simple('clicked', array($this, 'export'), $bcombo);
        $filterpanel->pack_end($exportbtn, false, false);
        
        //Add filter panel to the top
        $vbox->pack_start($filterpanel, false, false);

        $this->productview = new ProductListView();
        $scrwin = new GtkScrolledWindow();
        $scrwin->add($this->productview);
        $scrwin->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        
        $vbox->pack_start($scrwin);
    }
    
    public function populate(BranchesComboBox $combo)
    {
        $dbm = THSModel::singleton();
        $productList = $dbm->getProductList(true);
        //$dbm->close();
        $model = $this->productview->get_model();
        $model->clear();
        
        Main::refresh();
        
        $iter = $combo->get_active_iter();
        
        if ($iter == null){
            return false;
        }
        
        //Get branch id to get stock from
        $branch = $combo->getSelected();
        if (!is_object($branch)){
            return false;
        }
        
        $bid = $branch->id;
        Main::debug($bid);
        
        foreach ($productList as $product_id){
            $p = Product::getFromId($product_id);
            
            if ($p->stock[$bid] <= 0){
                continue;
            }
            
            $model->append(array (
                $p->id, 
                $p->partnumber,
                $p->description,
                ($p->state == Product::STATE_NEW)? 'Nuevo':'Usado',
                $p->price,
                $p->stock[$bid]
            ));
        }
    }
    
    /**
     * 
     * @param BranchesComboBox $bcombo
     */
    public function export($bcombo)
    {
        require_once 'fpdf.php';
        
        $view = $this->productview;
        $plist = $view->getList();
        
        $tmppath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'thsstock.pdf';
        $branch = $bcombo->getSelected();
        
        DocumentFactory::generateAvailableStockList($plist, $branch, $tmppath);
        
        if (strstr(strtolower((PHP_OS)), 'win')){
            exec("start /B $tmppath");
        }else if (strstr(PHP_OS, 'Linux')){
            exec("xdg-open $tmppath");
        }
    }
}
