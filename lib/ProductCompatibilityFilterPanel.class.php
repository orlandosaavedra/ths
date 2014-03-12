<?php

/**
 * Description of CompatibilityFilterBox
 *
 * @author orlando
 */
class ProductCompatibilityFilterPanel extends GtkHBox
{
    const MATCH_ALL = 'TODOS';
    
    /**
     *
     * @var ProductCompatibilityFilterPanelComboBox
     */
    public $model;
    /**
     *
     * @var ProductCompatibilityFilterPanelComboBox
     */
    public $version;
    /**
     *
     * @var ProductCompatibilityFilterPanelComboBox
     */
    public $other;
    /**
     *
     * @var ProductCompatibilityFilterPanelComboBox
     */
    public $year_from;
    /**
     *
     * @var ProductCompatibilityFilterPanelComboBox
     */
    public $year_to;
    
    public function __construct()
    {
        parent::__construct();
        $this->buildComboBoxes();
        $this->construct();
        $this->changed();
    }
    
    protected function buildComboBoxes()
    {
        $this->model = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->version = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->other = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->year_from = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        $this->year_to = new ProductCompatibilityFilterPanelComboBox(GObject::TYPE_STRING);
        
        /** Connecting **/
        $this->model->connect('on-select', array($this, 'changed'));
        $this->version->connect('on-select', array($this, 'changed'));
        $this->year_from->connect('on-select', array($this, 'changed'));
        $this->year_to->connect('on-select', array($this, 'changed'));
    }
    
    private function construct()
    {        
        /** Packing **/
        $this->pack_start(new GtkLabel('Modelo:'), false, false);
        $this->pack_start($this->model);
        $this->pack_start(new GtkLabel('Version:'), false, false);
        $this->pack_start($this->version);
        $this->pack_start(new GtkLabel('Otros:'), false, false);
        $this->pack_Start($this->other);
        $this->pack_start(new GtkLabel('Desde:'), false, false);
        $this->pack_start($this->year_from);
        $this->pack_start(new GtkLabel('Hasta:'), false, false);
        $this->pack_start($this->year_to);
        
    }
    
    public function changed($combo=null)
    {
        //List of years for years comboboxes
        $years = array();
        $current = (int) date('Y');
        for ($i=$current;$i>1963;$years[]=$i--);
        
        $dbm = THSModel::singleton();
        $filter = $this->getActiveFilter();
        
        if ($combo === null){
            $vmodels = $dbm->getProductCompatibilityModelList();
            $this->model->populate($vmodels);
            $this->version->lock();
            $this->other->lock();
            $this->year_from->populate($years);
            //$this->year_from->lock();
            $this->year_to->lock();
        }
        
        if ($combo === $this->model){   
            if ($filter->model){
                $versions = $dbm->getProductCompatibilityVersionList($filter->model);
                $this->version->populate($versions);
                $others = $dbm->getProductCompatibilityOtherList($filter->model, null);
                $this->other->populate($others);
                $this->year_from->populate($years);
            }else{
                $this->version->lock();
                $this->other->lock();
            }
        }
        
        if ($combo === $this->version){
            if ($filter->version){
                $others = $dbm->getProductCompatibilityOtherList($filter->model, $filter->version);
                $this->other->populate($others);
                $this->year_from->populate($years);
            }else{
                $this->other->lock();
            }
        }
        
        if ($combo === $this->year_from){
            $current = (int) date('Y');
            $min = (int) $this->year_from->get_active_text();
            $this->year_to->get_model()->clear();
            $years = array();
            for ($i=$current;$i>=$min;$years[]=$i--);
            $this->year_to->populate($years);
        }
        
        return false;
    }
    
    /**
     * 
     * @return \ProductCompatibility
     */
    public function getActiveFilter()
    {
        $pc = new ProductCompatibility();
        $pc->model = strtoupper($this->model->get_active_text());
        $pc->version = strtoupper($this->version->get_active_text());
        $pc->other = $this->other->get_active_text();
        $pc->year_from = $this->year_from->get_active_text();
        $pc->year_to = $this->year_to->get_active_text();
       
        return $pc;
    }
}
