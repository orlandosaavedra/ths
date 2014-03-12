<?php

/**
 * Description of CompatibilityFilterComboBox
 *
 * @author orlando
 */
class ProductCompatibilityFilterPanelComboBox extends GtkComboBox
{
    const MATCH_ALL = 'TODOS';
    
    /**
     *
     * @var gsignal
     */
    public $__gsignals = array(
        'on-select' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),        
    );
    
    public function __construct() 
    {
        $model = new GtkListStore(GObject::TYPE_STRING);
        parent::__construct($model);
        $crt = new GtkCellRendererText();
        $this->pack_start($crt);
        $this->set_attributes($crt, 'text', 0);
        $this->connect_simple('changed', array($this, '__do_changed'));
    }
    
    public function __do_changed()
    {
        if ($this->get_active_text() === null){
            return true;
        }else{
            echo 'emiting changed';
            $this->emit('on-select');
            return false;
        }
    }
    
    public function populate($data)
    {
        $this->set_sensitive(true);
        $model = $this->get_model();
        $model->clear();
        
        if (null != $data){
            foreach ($data as $row){
                if ($row===null){
                    //$row = ProductCompatibility::MATCH_ALL;
                }
                $model->append(array($row));
            }
        }else{
            //$model->append(array(ProductCompatibility::MATCH_ALL));
        }
    }
    
    public function lock()
    {
        $model = $this->get_model();
        $model->clear();
        $this->set_sensitive(false);
    }
    
    
    public function get_active_text()
    {
        //return parent::get_active_text();
        $model = $this->get_model();
        $iter = $this->get_active_iter();
        if ($iter === null){
            return null;
        }else{
            return $model->get_value($iter, 0);
        }
    }
}

Gobject::register_type('ProductCompatibilityFilterPanelComboBox');