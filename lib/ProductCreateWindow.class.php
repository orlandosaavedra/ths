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
        $this->build();
    }
    
    /**
     * Builds GUI
     */
    private function build()
    {
        $this->set_title('Crear Producto');
        $vbox = new GtkVBox();
        $this->add($vbox);
        
        $this->general = new ProductGeneralFrame();
        $this->general->set_border_width(5);
        $this->codes = new ProductCodesFrame();
        $this->codes->set_border_width(5);
        $this->codes->set_size_request(-1, 200);
        $this->category = new ProductCategoryFrame();
        $this->compatibility = new ProductCompatibilityFrame();
        $this->compatibility->set_size_request(-1, 200);
        $this->stock= new ProductStockFrame();
        
        $vbox->pack_start($this->general, false, false, 5);
        $hbox = new GtkHBox();        
        $vbox->pack_start($this->codes);
        $vbox->pack_start($this->compatibility);
        $vbox->pack_start($this->category, false, false);
        $vbox->pack_start($this->stock, false, false);        
        
        
        $this->createbtn = new GtkButton('Crear');
        $this->createbtn->connect_simple('clicked', array($this, 'create'));
        //$this->createbtn->set_size_request(-1, 50);
        $this->cancelbtn = new GtkButton('Cancelar');
        $this->cancelbtn->connect_simple('clicked', array($this, 'destroy'));
        $hbox = new GtkHBox();
        
        $hbox->pack_start($this->cancelbtn, true, true, 10);
        $hbox->pack_start($this->createbtn, true, true, 10);
        $vbox->pack_end($hbox, false, false, 10);
    }
    
    public function create()
    {
        //Open db link
        $dbm = new THSModel;
        
        //Get product generals
        $product = $this->general->getProduct();
        //get selected category
        $category = $this->category->getSelectedCategory();
        //If selected category is null, leave it otherwise assign id
        $product->category_id = ($category == null) ?: $category->id;
        //Check that product has a descriptino and it is bigger than 3 chars
        If (trim($product->description)==null || strlen($product->description)<3){
            $this->general->notify('Por favor agregue una descripción de mas de 3 letras');
            return false;
        }
        
        //Create the product in DB
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
            $compatibility->product_id = $id;
            $dbm->addProductCompatibility($compatibility);
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
        $this->compatibility->clearFilter();
        $this->codes->clear();
        $this->category->populate();
        
        $diag->destroy();
    }
}