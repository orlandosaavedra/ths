<?php


/**
 * Description of ProductGeneralFrame
 *
 * @author orlando
 */
class ProductGeneralFrame extends GtkFrame
{
    /**
     *
     * @var GtkEntry
     */
    public $productId;
    /**
     *
     * @var GtkEntry
     */
    public $productPartnumber;
    /**
     *
     * @var GtkRadioButton
     */
    public $productStateNew;
    /**
     *
     * @var GtkRadioButton
     */
    public $productStateUsed;
    
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
    
    //protected $notifyLabel = null;

    public function __construct()
    {
        parent::__construct('General');
        $this->set_border_width(3);
        $this->productId = $id = new GtkEntry();
        //$this->notifyLabel = new GtkLabel();
        //$this->notifyLabel->set_alignment(0.5, 0.5);
        $id->set_max_length(10);
        $id->set_size_request(120, -1);
        $id->connect('focus-out-event', array($this, 'validateId'));
        $id->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        
        $this->productPartnumber = $partn = new GtkEntry();
        $partn->set_max_length(50);
        $partn->set_size_request(200, -1);
        $partn->connect('focus-out-event', array($this, 'validateExistence'));
        
        $this->productStateNew = null;
        $this->productStateNew = new GtkRadioButton($this->productStateNew, 'Nuevo');
        $this->productStateUsed= new GtkRadioButton($this->productStateNew, 'Usado');
        //$this->productStateNew->connect_simple('focus-out-event', array($this, 'validateExistence'), $partn);
        $this->productStateNew->connect_simple('toggled', array($this, 'validateExistence'), $partn);
        $this->productCost = $cost = GtkSpinButton::new_with_range(0, 9999999999, 1000);
        $this->productPrice = $price = GtkSpinButton::new_with_range(0, 9999999999, 1000);
        $this->productDescription = $description = new GtkEntry();
        $description->set_max_length(200);
        
        $this->productCost->connect(
                'value-changed',
                function($cost, $price){
                    $v = $cost->get_value();
                    $price->set_value($v*1.3);
                }, $this->productPrice);
        
        /**
         * Packing
         */
        $vbox = new GtkVBox();
        $this->add($vbox);
        
        //$vbox->pack_start($this->notifyLabel);
        
        $row = array();
        $row[0] = new GtkHBox();
        $lcode = new GtkLabel('Código interno:');
        $lcode->set_size_request(120, -1);
        $lcode->set_alignment(1, 0.5);
        $row[0]->pack_start($lcode, false, false);
        $row[0]->pack_start($id, false, false);
        //$hbox = new GtkHBox;
        $lpart = new GtkLabel('Numero de Parte:');
        $lpart->set_size_request(120, -1);
        $lpart->set_alignment(1, 0.5);
        $row[0]->pack_start($lpart, false, false);
        $row[0]->pack_start($partn, false, false);
        
        $row[1] = new GtkHBox;
        $lcond = new GtkLabel('Condición:');
        $lcond->set_size_request(120, -1);
        $lcond->set_alignment(1, 0.5);
        $row[1]->pack_start($lcond, false, false);
        $row[1]->pack_start($this->productStateNew, false, false);
        $row[1]->pack_start($this->productStateUsed, false, false);
        $row[1]->pack_start(new GtkLabel());
        
        $row[2] = new GtkHBox();
        $lcost = new GtkLabel('Costo:');
        $lcost->set_size_request(120, -1);
        $lcost->set_alignment(1, 0.5);
        $lprice = new GtkLabel('Precio:');
        $lprice->set_size_request(120, -1);
        $lprice->set_alignment(1, 0.5);
        $row[2]->pack_start($lcost, false, false);
        $row[2]->pack_start($cost, false, false);
        $row[2]->pack_start($lprice, false, false);
        $row[2]->pack_start($price, false,false);
        $row[2]->pack_start(new GtkLabel());
        
        $row[3] = new GtkHBox;
        $ldesc = new GtkLabel('Descripción:');
        $ldesc->set_size_request(120, 30);
        $ldesc->set_alignment(1, 0.5);
        $row[3]->pack_start($ldesc, false, false);
        $row[3]->pack_start($description);
        
        foreach ($row as $hbox){
            $vbox->pack_start($hbox, false, false);
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
        $pn = trim($this->productPartnumber->get_text());
        
        if ($pn == null){
            return false;
        }
        
        $state = ($this->productStateNew->get_active())? Product::STATE_NEW :Product::STATE_USED;
        $sql = "SELECT `id` FROM `product` WHERE `partnumber`='$pn'"
                . " AND `state`='$state'";
        
        $id = trim($this->productId->get_text());
        
        if ($id != null){
            $sql .= " AND `id`!='{$id}'";
        }
        
        $result = $dbm->query($sql);
        
        Main::debug($dbm->error);
        
        //$dbm->close();
        
        if ($result->num_rows){
            if ($state == Product::STATE_NEW && $this->productStateUsed->get_sensitive() == true){
                $this->productStateNew->set_sensitive(false);
                $this->productStateUsed->set_active(true);
                
            }else if ($state == Product::STATE_USED && $this->productStateNew->get_sensitive() == true){
                $this->productStateUsed->set_sensitive(false);
                $this->productStateNew->set_active(true);
                
            }else {
                $this->notify('El numero de parte ya existe USADO y NUEVO');
                $this->productStateNew->set_sensitive(true);
                $this->productStateUsed->set_sensitive(true);
                $this->productPartnumber->set_text('');
                
            }
            
        }else{
            $this->productStateNew->set_sensitive(true);
            $this->productStateUsed->set_sensitive(true);
        }
        
        return false;
    }
    
    public function getProduct()
    {
        $product = new Product();
        $product->id = ($this->productId->get_text())?: null;
        $product->partnumber = ($this->productPartnumber->get_text())?: null;
        $product->description = $this->productDescription->get_text();
        $product->cost = $this->productCost->get_value();
        $product->price = $this->productPrice->get_value();
        $product->state = ($this->productStateNew->get_active())? Product::STATE_NEW :Product::STATE_USED;
        return $product;
    }
    
    public function clear()
    {
        $this->productStateNew->set_active(true);
        $this->productId->set_text('');
        $this->productPartnumber->set_text('');
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
        $this->productId->set_editable(!$bool);
        $this->productPartnumber->set_editable(!$bool);
        $this->productDescription->set_editable(!$bool);
        $this->productPrice->set_sensitive(!$bool);
        $this->productStateNew->set_sensitive(!$bool);
        $this->productStateUsed->set_sensitive(!$bool);
    }
    
    public function blockExistent($bool)
    {
        $this->blockexistent = $bool;
    }
    
}