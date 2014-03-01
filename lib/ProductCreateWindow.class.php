<?php
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
     * @var ProductCodesFrame
     */
    public $codes;
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
        $this->codes = new ProductCodesFrame();
        $this->category = new ProductCategoryFrame();
        $this->compatibility = new ProductCompatibilityFrame();
        $this->stock= new ProductStockFrame();
        
        $vbox->pack_start($this->general, false, false, 5);
        $hbox = new GtkHBox();        
        $vbox->pack_start($this->codes);
        $vbox->pack_start($this->compatibility);
        $vbox->pack_start($this->category, false, false);
        $vbox->pack_start($this->stock, false, false);        
        
        
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
        
        $codes = $this->codes->getCodes();
        
        foreach ($codes as $code){
            $dbm->addProductCode($id, $code->reference, $code->code);
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