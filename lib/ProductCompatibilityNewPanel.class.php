<?php

/**
 * Description of ProductCompatibilityNewPanel
 *
 * @author orlando
 */
class ProductCompatibilityNewPanel extends GtkHBox
{
    /**
     *
     * @var ProductCompatibilityEntry
     */
    public $model;
    
    public function __construct() 
    {
        parent::__construct();
        $this->createEntries();
        $this->configureEntries();
        $this->layout();
        
        $years = array();
        $current = (int) date('Y');
        for ($i=$current;$i>1963;$years[]=$i--);
        $this->year_from->populate($years);
    }
    
    protected function configureEntries()
    {
        $dbm = THSModel::singleton();
        $models = $dbm->getProductCompatibilityModelList();
        $versions = $dbm->getProductCompatibilityVersionList();
        $others = $dbm->getProductCompatibilityOtherList();
        
        $this->setEntryCompletion($this->model, $models);
        $this->setEntryCompletion($this->version, $versions);
        $this->setEntryCompletion($this->other, $others);
    }
    
    private function setEntryCompletion($entry, $values)
    {
        $completion = new GtkEntryCompletion();
        $lstore = new GtkListStore(GObject::TYPE_STRING);
        
        foreach ($values as $value){
            if ($value == null){
                continue;
            }
            
            $lstore->append(array($value));
        }
        
        $completion->set_model($lstore);
        $completion->set_text_column(0);
        $entry->set_completion($completion);
    }
    
    protected function createEntries() 
    {
        $this->model = new GtkEntry(); //new ProductCompatibilityEntry();
        $this->version = new GtkEntry(); //new ProductCompatibilityEntry();
        $this->other = new GtkEntry(); //new ProductCompatibilityEntry();
        $this->year_from = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->year_to = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        
        /** Connecting **/
        $this->year_from->connect('on-select', array($this, 'changed'));
    }
    
    public function layout()
    {
        $layout = array(
            'Modelo'=>$this->model,
            'Version'=>$this->version,
            'Otros'=>$this->other,
            'Desde'=>$this->year_from,
            'Hasta'=>$this->year_to
        );
        
        foreach ($layout as $text => $widget){
            $label = new GtkLabel($text);
            $this->pack_start($label);
            $this->pack_start($widget);
        }
    }
    
    public function changed($combo=null)
    {
        $years = array();
        $current = (int) date('Y');
        
        if ($combo === $this->year_from){
            
            $min = (int) $this->year_from->get_active_text();
            for ($i=$current;$i>=$min;$years[]=$i--);
            $this->year_to->populate($years);
        }
    }
}
