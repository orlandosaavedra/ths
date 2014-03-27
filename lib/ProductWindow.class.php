<?php
/**
 * Description of IncomeCreateWindow
 *
 * @author orlando
 */
class ProductWindow extends GtkWindow
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
    
    /** FLAGS **/
    protected $pid = null;
    protected $editable = false;
    
    /**
     * 
     */
    public function __construct($pid=null, $editable=false)
    {
        parent::__construct();
        $this->pid = $pid;
        $this->editable = $editable;
        $this->build();
    }
    
    /**
     * Builds GUI
     */
    protected function build()
    {
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
        
        if ($this->editable){
            $this->createbtn = new GtkButton('Crear');
            $this->createbtn->connect_simple('clicked', array($this, 'create'));
            $this->cancelbtn = new GtkButton('Cancelar');
            $this->cancelbtn->connect_simple('clicked', array($this, 'destroy'));
            $hbox = new GtkHBox();

            $hbox->pack_start($this->cancelbtn, true, true, 10);
            $hbox->pack_start($this->createbtn, true, true, 10);
            $vbox->pack_end($hbox, false, false, 10);
        }else{
            $this->general->lock();
            $this->codes->lock();
            $this->compatibility->lock();
            $this->category->lock();
            $this->stock->lock();
        }
        
        if ($this->pid !== null){
            $this->populate();
        }
    }
    
    /**
     * Fills the product fields
     * @param Product $product
     */
    public function populate()
    {
        $dbm = THSModel::singleton();
        $product = Product::fetch($this->pid);

        $this->general->id->set_text($product->id);
        $this->general->id->set_sensitive(false);
        $this->general->partnumber->set_text($product->partnumber);
        $this->general->cost->set_text((double)$product->cost);
        $this->general->price->set_text((double)$product->price);
        $this->general->description->set_text($product->description);
        
        $codes = $dbm->getProductCodes($product->id);
        $this->codes->populate($codes);
        
        $this->general->condition->setActive($product->state);
        
        $this->category->combo->setActive($product->category_id);
        
        $this->stock->setStock($dbm->getProductStock($product->id));
        
        $data = $dbm->getProductCompatibility($product->id);

        foreach ($data as $pc){
            //FIXME need to change storeCompatibility entry param
            $this->compatibility->storeCompatibility($pc);
        }
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