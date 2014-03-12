<?php

/**
 * Description of ProductCompatibilityNewPanel
 *
 * @author orlando
 */
class ProductCompatibilityNewPanel extends ProductCompatibilityFilterPanel
{
    public function __construct() 
    {
        parent::__construct();
        $this->populateOptions();
        
        $years = array();
        $current = (int) date('Y');
        for ($i=$current;$i>1963;$years[]=$i--);
        $this->year_from->populate($years);
    }
    
    protected function populateOptions()
    {
        $dbm = THSModel::singleton();
        $models = $dbm->getProductCompatibilityModelList();
        $versions = $dbm->getProductCompatibilityVersionList();
        $others = $dbm->getProductCompatibilityOtherList();
        
        //Clearing
        $this->model->get_model()->clear();
        $this->version->get_model()->clear();
        $this->other->get_model()->clear();
        
        $this->model->append_text(self::MATCH_ALL);
        foreach ($models as $available){
            if ($available === null){ continue; }
            $this->model->append_text($available);
        }
        
        $this->version->append_text(self::MATCH_ALL);
        foreach ($versions as $available){
            if ($available === null) { continue; }
            $this->version->append_text($available);
        }
        
        $this->other->append_text(self::MATCH_ALL);
        foreach ($others as $available){
            if ($available === null) { continue; }
            $this->other->append_text($available);
        }
        
    }
    
    protected function buildComboBoxes() 
    {
        $this->model = new ProductCompatibilityNewPanelComboBox();
        $this->version = new ProductCompatibilityNewPanelComboBox();
        $this->other = new ProductCompatibilityNewPanelComboBox();
        $this->year_from = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->year_to = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        
        /** Connecting **/
        //$this->model->connect('on-select', array($this, 'changed'));
        //$this->version->connect('on-select', array($this, 'changed'));
        $this->year_from->connect('on-select', array($this, 'changed'));
        //$this->year_to->connect('on-select', array($this, 'changed'));
    }
    
    public function changed($combo=null)
    {
        
        $years = array();
        $current = (int) date('Y');
        /*
        if($combo===null){
            for ($i=$current;$i>1963;$years[]=$i--);
            $this->year_from->populate($years);
            $this->model->clearEntry();
            $this->version->clearEntry();
            $this->other->clearEntry();
            $this->populateOptions();
        }*/
        
        if ($combo === $this->year_from){
            
            $min = (int) $this->year_from->get_active_text();
            for ($i=$current;$i>=$min;$years[]=$i--);
            $this->year_to->populate($years);
        }
    }
}
