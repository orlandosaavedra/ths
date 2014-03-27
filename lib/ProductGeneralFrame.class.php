<?php


/**
 * Description of ProductGeneralFrame
 *
 * @author orlando
 */
class ProductGeneralFrame extends GtkFrame
{
    
    protected $entries = array(
        'id' => null,
        'partnumber' => null,
        'state' => null,
        'procedence' => null,
        'cost'=>null,
        'price'=>null,
        'description'=>null
        
    );
    /**
     *
     * @var GtkEntry
     */
    public $id;
    /**
     *
     * @var GtkEntry
     */
    public $partnumber;
    /**
     *
     * @var ProductConditionComboBox
     */
    public $condition;
    
    /**
     * @var GtkEntry
     */
    public $origin;
    
    /**
     *
     * @var GtkSpinButton
     */
    public $productCost;
    /**
     *
     * @var GtkSpinButton
     */
    public $productPrice;
    /**
     *
     * @var GtkEntry
     */
    public $productDescription;
    
    protected $blockexistent = true;

    public function __construct()
    {
        parent::__construct();
        $this->createEntries();
        $this->configureEntries();
        $this->layout();
    }
    
    protected function createEntries()
    {
        //product->id
        $this->id = new GtkEntryNumeric(10);
        $this->id->set_size_request(120, -1);
        //product->partnumber
        $this->partnumber = new GtkEntry();
        $this->partnumber->set_max_length(50);
        //product->condition
        $this->condition = new ProductConditionComboBox();
        //product->origin
        $this->origin = new GtkEntry();
        
        $this->cost = new GtkEntryNumeric();
        $this->price = new GtkEntryNumeric();
        
        $this->description = new GtkEntry();  
        $this->description->set_max_length(200);
        $this->description->set_size_request(600, -1);
    }
    
    protected function configureEntries()
    {
        $dbm = new THSModel();
        $completion = new GtkEntryCompletion();
        $lstore = new GtkListStore(GObject::TYPE_STRING);
        
        
        $origins = $dbm->getProductOriginList();
        foreach ($origins as $origin){
            $lstore->append(array($origin));
        }
        
        $completion->set_model($lstore);
        $this->origin->set_completion($completion);
    }
    
    protected function layout()
    {
        $vbox = new GtkVBox();
        $this->add($vbox);
        
        $layout = array(
            0 => array (
                'Codigo' => $this->id,
                'N° Parte' =>  $this->partnumber,
                'Condición' => $this->condition
            ),
            1 => array(
                'Origen' => $this->origin,
                'Costo' => $this->cost,
                'Precio' => $this->price
            ),
            2 => array(
                'Descripción' => $this->description
            )
        );
        
        foreach ($layout as $row){
            $hbox = new GtkHBox();
            foreach ($row as $text => $widget){
                $label = new GtkLabel($text);
                $label->set_size_request(100, -1);
                $label->set_alignment(1, 0.5);
                $hbox->pack_start($label, false, false);
                $hbox->pack_start($widget, false, false);
            }
            
            $vbox->pack_start($hbox);
        }
    }
    
    /**
     * 
     * @param GtkEntry $entry
     */
    public function validateId(GtkEntry $entry)
    {        
        if (!$this->blockexistent){
            return false;
        }
        
        $dbm = new THSModel;
        
        if ($dbm->getProduct($entry->get_text())){
            $this->notify('Codigo de producto ya existe');
            $entry->set_text('');
            $entry->grab_focus();
        }
        
        //$dbm->close();
        return false;
    }
    
    public function notify($msg)    
    {
        $dialog = new GtkMessageDialog(
                $this->get_toplevel(),
                0,
                Gtk::MESSAGE_ERROR,
                Gtk::BUTTONS_OK,
                $msg);
        $dialog->run();
        $dialog->destroy();
        //$this->notifyLabel->set_markup('<span color="red">'.$msg.'</span>');
    }
    
    public function validateExistence(GtkEntry $entry)
    {
        if (!$this->blockexistent){
            return false;
        }
        
        $dbm = THSModel::singleton();
        $pn = trim($this->partnumber->get_text());
        
        if ($pn == null){
            return false;
        }
        
        $state = ($this->condition->get_active())? Product::STATE_NEW :Product::STATE_USED;
        $sql = "SELECT `id` FROM `product` WHERE `partnumber`='$pn'"
                . " AND `state`='$state'";
        
        $id = trim($this->id->get_text());
        
        if ($id != null){
            $sql .= " AND `id`!='{$id}'";
        }
        
        $result = $dbm->query($sql);
        
        Main::debug($dbm->error);
        
        //$dbm->close();
        
        if ($result->num_rows){
            if ($state == Product::STATE_NEW && $this->productStateUsed->get_sensitive() == true){
                $this->condition->set_sensitive(false);
                $this->productStateUsed->set_active(true);
                
            }else if ($state == Product::STATE_USED && $this->condition->get_sensitive() == true){
                $this->productStateUsed->set_sensitive(false);
                $this->condition->set_active(true);
                
            }else {
                $this->notify('El numero de parte ya existe USADO y NUEVO');
                $this->condition->set_sensitive(true);
                $this->productStateUsed->set_sensitive(true);
                $this->partnumber->set_text('');
                
            }
            
        }else{
            $this->condition->set_sensitive(true);
            $this->productStateUsed->set_sensitive(true);
        }
        
        return false;
    }
    
    public function getProduct()
    {
        $product = new Product();
        $product->id = $this->id->get_text();
        $product->partnumber = $this->partnumber->get_text();
        $product->description = $this->description->get_text();
        $product->origin = $this->origin->getActive();
        $product->cost = $this->cost->get_text();
        $product->price = $this->price->get_text();
        $product->state = $this->condition->getActive();
        return $product;
    }
    
    public function clear()
    {
        $this->condition->set_active(true);
        $this->id->set_text('');
        $this->partnumber->set_text('');
        $this->productCost->set_value(0);
        $this->productPrice->set_value(0);
        $this->productDescription->set_text('');
        Main::refresh();
    }
    
    public function display(Product $product)
    {
        
    }
    
    public function lock($bool=true)
    {
        $this->id->set_editable(!$bool);
        $this->partnumber->set_editable(!$bool);
        $this->productDescription->set_editable(!$bool);
        $this->productPrice->set_sensitive(!$bool);
        $this->condition->set_sensitive(!$bool);
        $this->productStateUsed->set_sensitive(!$bool);
    }
    
    public function blockExistent($bool)
    {
        $this->blockexistent = $bool;
    }
    
}