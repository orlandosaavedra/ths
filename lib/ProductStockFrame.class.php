<?php

/**
 * Description of ProductStockFrame
 *
 * @author orlando
 */
class ProductStockFrame extends GtkFrame
{
    public $stock = array();
    /**
     * Builds initial stock frame
     */
    public function __construct()
    {
        parent::__construct('Stock');
        $model = new THSModel;
        $hbox = new GtkHbox();
        $this->set_border_width(3);
        $this->add($hbox);
        
        foreach ($model->getBranches() as $branch){
            //$hbox = new GtkHBox();
            $hbox->pack_start($label = new GtkLabel($branch->name), false, false);
            $label->set_size_request(150, -1);
            $hbox->pack_start($this->stock[$branch->id] = new GtkEntry('0'), false, false);
            $this->stock[$branch->id]->connect('key-press-event', array('GtkEntryMasker', 'numeric'));
            $this->stock[$branch->id]->set_size_request(60, -1);
            $this->stock[$branch->id]->set_alignment(0.5);
            //$vbox->pack_start($hbox);
        }        
    }
    
    /**
     * 
     * @return array with stock (values) for each branch id (key)
     */
    public function getStock()
    {
        $stock = array();
        
        foreach ($this->stock as $branch => $widget){
            $stock[$branch] = (int)$widget->get_text();
        }
        
        return $stock;
        
    }
    
    /**
     * 
     * @param array $values branchid=>stock pairs
     */
    public function setStock($values)
    {
        if(!is_array($values)){
            $backtrace = debug_backtrace();
            $message = __CLASS__.__METHOD__. ' argument must be an array';
            $message .= ' '.gettype($values).' given at '.$backtrace[0]['file'];
            $message .= ':'.$backtrace[0]['line'];
            throw new Exception ($message);
        }
        
        foreach ($values as $branchid => $stock){
            if (key_exists($branchid, $this->stock)){
                $this->stock[$branchid]->set_text($stock);
            }
        }
    }
    
    public function clear()
    {
        foreach ($this->stock as $spin){
            $spin->set_text('0');
        }
    }
    
    public function lock()
    {
        foreach ($this->stock as $bid => $wdg){
            $wdg->set_editable(false);
        }
    }
}
