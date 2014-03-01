<?php

/**
 * Description of CompatibilityFilterBox
 *
 * @author orlando
 */
class CompatibilityFilterHBox extends GtkHBox
{
    const MATCH_ALL = 'TODOS';
    
    /**
     *
     * @var CompatibilityFilterComboBox
     */
    public $model;
    /**
     *
     * @var CompatibilityFilterComboBox
     */
    public $startyear;
    /**
     * @var CompatibilityFilterComboBox
     */
    public $endyear;
    public $version;
    public $transmision;
    
    public function __construct()
    {
        parent::__construct();
        $this->construct();
        $this->changed();
    }
    
    private function construct()
    {
        $this->model = new CompatibilityFilterComboBox(GObject::TYPE_STRING);
        $this->startyear = new CompatibilityFilterComboBox(GObject::TYPE_LONG);
        $this->endyear = new CompatibilityFilterComboBox(GObject::TYPE_LONG);
        $this->version = new CompatibilityFilterComboBox(GObject::TYPE_STRING);
        $this->transmision = new CompatibilityFilterComboBox(GObject::TYPE_STRING);
        
        /** Connecting **/
        
        $this->model->connect('on-select', array($this, 'changed'));
        $this->startyear->connect('on-select', array($this, 'changed'));
        $this->endyear->connect('on-select', array($this, 'changed'));
        $this->version->connect('on-select', array($this, 'changed'));
        //$this->transmision->connect('changed', array($this, 'changed'));
        
        /** Packing **/
        $this->pack_start(new GtkLabel('Modelo:'), false, false);
        $this->pack_start($this->model);
        $this->pack_start(new GtkLabel('Desde:'), false, false);
        $this->pack_start($this->startyear);
        $this->pack_start(new GtkLabel('Hasta'), false, false);
        $this->pack_start($this->endyear);
        $this->pack_start(new GtkLabel('Version:'), false, false);
        $this->pack_start($this->version);
        $this->pack_start(new GtkLabel('Transmisión'), false, false);
        $this->pack_start($this->transmision);
    }
    
    public function changed($combo=null)
    {
        Main::debug('changed activated');
        $dbm = new THSModel();
        $compatibility = $this->getActiveCompatibility();
        
        if ($combo === null){
            $vmodels = $dbm->getVehicleModels();
            $this->model->populate($vmodels);
            $this->startyear->lock();
            $this->endyear->lock();
            $this->version->lock();
            $this->transmision->lock();
            return false;
        }
        
        if ($combo === $this->model){
            
            $syears = $dbm->getVehicleModelYears($compatibility->model);
            $this->startyear->populate($syears);
            $this->endyear->lock();
            $this->version->lock();
            $this->transmision->lock();
            return true;
        }
        
        if ($combo === $this->startyear){
            $eyears = $dbm->getVehicleModelYears($compatibility->model);
            $years = array();
            
            if($compatibility->startyear!==null){
                for ($i=0;$i<count($eyears);++$i){
                    if((int)$eyears[$i]>=$compatibility->startyear){
                        $years[]= $eyears[$i];
                    }
                }
            }
            
            $this->endyear->populate($years);
            $this->transmision->lock();
            $this->version->lock();
            
            return true;
        }
        
        if ($combo === $this->endyear){
            
            $versions = $dbm->getVehicleModelVersions(
                    $compatibility->model,
                    $compatibility->startyear,
                    $compatibility->endyear);
            $this->version->populate($versions);
            $this->transmision->lock();
            return true;
        }
        
        if ($combo===$this->version){
            $trans = $dbm->getVehicleModelTransmissions(
                    $compatibility->model,
                    $compatibility->startyear,
                    $compatibility->endyear,
                    $compatibility->version);
            $this->transmision->populate($trans);
            return true;
        }
    }
    
    public function getActiveCompatibility()
    {
        $model = $this->model->get_active_text();
        if ($model === null){
            return null;
        }
        
        if ($model === CompatibilityFilterComboBox::MATCH_ALL){
            $model = null;
        }
        $syear = $this->startyear->get_active_text();
        if ($syear===CompatibilityFilterComboBox::MATCH_ALL){
            $syear=null;
        }
        $eyear = $this->endyear->get_active_text();
        if($eyear===CompatibilityFilterComboBox::MATCH_ALL){
            $eyear=null;
        }
        $version = $this->version->get_active_text();
        if($version===CompatibilityFilterComboBox::MATCH_ALL){
            $version=null;
        }
        $trans = $this->transmision->get_active_text();
        if($trans===  CompatibilityFilterComboBox::MATCH_ALL){
            $trans=null;
        }
        $compatibility = new ProductCompatibility;
        $compatibility->model = $model;
        $compatibility->startyear=($syear)?:null;
        $compatibility->endyear=($eyear)?:null;
        $compatibility->version=$version;
        $compatibility->transmission=$trans;
        return $compatibility;
    }
}
