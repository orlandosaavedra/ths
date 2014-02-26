<?php

/**
 * Description of IncomeCreateWindow
 *
 * @author orlando
 */
class ProductModifyWindow extends ProductCreateWindow
{
    
    public function __construct($product_id)
    {
        parent::__construct();
        $vbox = $this->get_child();
        $this->set_title('Modificar Producto');
        $this->populate($product_id);
        $this->createbtn->get_child()->set_text('Modificar');
    }
    
    /**
     * Fills the product fields
     * @param Product $product
     */
    public function populate($product_id)
    {
        $dbm = new THSModel();
        $product = Product::getFromId($product_id);

        $this->general->productId->set_text($product->id);
        $this->general->productId->set_sensitive(false);
        $this->general->productPartnumber->set_text($product->partnumber);
        $this->general->productCost->set_value((double)$product->cost);
        $this->general->productPrice->set_value((double)$product->price);
        $this->general->productDescription->set_text($product->description);
        
        // Sets product condition (New or Used)
        if ($product->state == Product::STATE_NEW){
            $this->general->productStateNew->set_active(true);
        }else{
            $this->general->productStateUsed->set_active(true);
        }
        
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
        $dbm = new THSModel;
        $product = $this->general->getProduct();
        $product->category = $this->category->getSelectedCategory()->id;
        $product->stock = $this->stock->getStock();
        
        if(!$dbm->updateProduct($product)){
            return;
        }
        
        //DIRTY
        $dbm->query("DELETE FROM `compatibility` WHERE `product_id`='{$product->id}'");
        
        $compatibilities = $this->compatibility->getCompatibilityStore();
        
        foreach ($compatibilities as $compatibility){
            $dbm->setProductCompatibility($product->id, $compatibility);
        }

        $diag = new GtkDialog(
                'Correcto',
                $this,
                Gtk::DIALOG_MODAL,
                array(Gtk::STOCK_OK, Gtk::RESPONSE_OK));

        $diag->vbox->add(new GtkLabel('Producto modificado'));
        $diag->show_all();
        $diag->run();
        $diag->destroy();
    }
    
    public function lock()
    {
        $this->general->lock();
        $this->category->lock();
        $this->compatibility->lock();
        $this->stock->lock();
        $this->createbtn->destroy();
        $this->cancelbtn->destroy();
    }
}