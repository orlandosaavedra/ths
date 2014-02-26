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
        
        $dbm = new THSModel();
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

        $this->productview = new ProductView();
        $scrwin = new GtkScrolledWindow();
        $scrwin->add($this->productview);
        $scrwin->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        
        $vbox->pack_start($scrwin);
    }
    
    public function populate(BranchesComboBox $combo)
    {
        $dbm = new THSModel();
        $productList = $dbm->getProductList(true);
        $dbm->close();
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
        
        $branch = $bcombo->getSelected();
        $plist = $this->productview->getList();
        
        DocumentFactory::generateStockList($branch, './');
        
        $fpdf = new fpdf('P', 'mm', 'Letter');
        $fpdf->setFont('Arial', '', 12);
        $fpdf->AddPage();
        $branchdesc = 'Sucursal: ('.$branch->id.')'.$branch->name;
        $fpdf->Cell(0, 10, 'Listado de stock disponible', 0, 1, 'C');
        $fpdf->Cell(20, 10, $branchdesc, 0, 1, 'L');
        $fpdf->Cell(100, 10, $branch->address, 0, 1, 'L');
        $fpdf->Line(10, 42, 205, 42);
        $fpdf->Ln();
        $fpdf->Cell(20, 8, 'Codigo', 1, 0, 'C');
        $fpdf->Cell(40, 8, 'Numero de Parte', 1, 0, 'C');
        $fpdf->Cell(80, 8, iconv('UTF8', 'cp1252','Descripción'), 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Estado', 1, 0, 'C');
        $fpdf->Cell(20, 8, 'Precio', 1, 0, 'C');
        $fpdf->Cell(15, 8, 'Stock', 1, 1, 'C');
        
        foreach ($plist as $pid){
            $product = Product::getFromId($pid);
            $fpdf->Cell(20, 8, $product->id, 1, 0, 'C');
            $fpdf->Cell(40, 8, $product->partnumber, 1, 0, 'C');
            $fpdf->Cell(80, 8, $product->description, 1, 0, 'L');
            $state = ($product->state)? 'Nuevo':'Usado';
            $fpdf->Cell(20, 8, $state, 1, 0, 'C');
            $fpdf->Cell(20, 8, $product->price, 1, 0, 'R');
            $fpdf->Cell(15, 8, $product->stock[$branch->id], 1, 1, 'C');
        }
        
        $fpdf->Output('/tmp/export.pdf', 'F');
    }
}
