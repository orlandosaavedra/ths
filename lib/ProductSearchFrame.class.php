<?php
/**
 * Description of SearchWindow
 *
 * @author orlando
 */
final class ProductSearchFrame extends GtkFrame
{
    /**
     *
     * @var ProductListView
     */
    public $view;
    
    private $_scrwin;
    /**
     *
     * @var GtkEntry
     */
    public $searchEntry;
    /**
     *
     * @var GtkButton
     */
    public $searchButton;
    /**
     *
     * @var GtkComboBox
     */
    public $modelCombo;
    
    /**
     *
     * @var ProductCompatibilityFilterPanel
     */
    public $compatibility;
    
    /**
     *
     * @var ProductCategoryComboBox
     */
    public $category;
    
    /**
     *
     * @var array
     */
    public $__gsignals = array(
        'search' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'activated' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'view-right-click'=>array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GOBject::TYPE_LONG, GtkRequisition::gtype))
        );
    
    public function __construct()
    {
        parent::__construct();
        $this->build();
        $this->doConnect();
    }
    
    protected function build()
    {
        $this->_buildEntryButtons();
        
        $this->compatibility = new ProductCompatibilityFilterPanel();
        $this->category = new ProductCategoryComboBox();
        $this->category->fetch();
        
        $this->_buildListView();
        $this->pack();
    }
    
    /**
     * Returns currently selected product (row);
     * @return \Product
     */
    public function getSelected()
    {
        list ($model, $iter) = $this->view->get_selection()->get_selected();
        $row = Product::getFromId($model->get_value($iter, 0));
        return $row;
    }
    
    protected function doConnect()
    {
        $this->view->connect_simple('row-activated', array($this, 'emit'), 'activated');
        $this->searchButton->connect_simple('clicked', array($this, 'emit'), 'search');
        $rcfunc = function($view, $event, $frame){
            if ($event->button===3){
                $frame->emit('view-right-click');
            }
            
            return false;
        };
        
        $this->view->connect('button-press-event', $rcfunc, $this);
    }
    
    protected function pack()
    {
        $hbox=new GtkHBox;
        $vbox=new GtkVBox;
        
        $this->add($vbox);
        
        $hbox->pack_start($this->searchEntry);
        $hbox->pack_start($this->searchButton, false, false, false  );
        
        $vbox->pack_start($hbox, false, false, false);
        $vbox->pack_start($this->compatibility, false, false);
        
        $hbox = new GtkHBox();
        $hbox->pack_start(new GtkLabel('Categoria:'), false, false);
        $hbox->pack_start($this->category);
        $vbox->pack_start($hbox, false, false);
        $vbox->pack_start($this->_scrwin);

    }
    
    private function _buildEntryButtons()
    {
        $this->searchEntry = new GtkEntry();
        $this->searchButton = new GtkButton('Buscar');
        $this->searchEntry->connect_simple('activate', array($this->searchButton, 'clicked'));
        
    }
    
    private function _buildListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $this->view = new ProductListView();
        $scrwin->add($this->view);
        
        $this->_scrwin = $scrwin;
    }
    
    /**
     * 
     * @return string
     */
    public function getSearch()
    {   
        return $this->searchEntry->get_text();
    }   
    
    public function appendResult(Product $product)
    {
        //$stock = $dbm->getProductStock($product->id);
        $model = $this->view->get_model();
        $data = array(
            $product->id,
            $product->partnumber,
            $product->description,
            ($product->state==Product::STATE_NEW)? 'Nuevo': 'Usado',
            $product->price,
            $product->getStock()
                );
        if (is_object($model)){
            $model->append($data);
        }else{
            return;
        }
    }
    
    public function clear()
    {
        $this->view->get_model()->clear();
    }
    
    public function getResults()
    {
        $model = $this->view->get_model();
        $iter = $model->get_iter_first();
        $ret = array();
        
        do {
            for ($i=0;$i<$model->get_n_columns();$i++){
                $product = Product::getFromId($model->get_value($iter, 0));
            }
            
            $ret[] = $product;
            $iter = $model->iter_next($iter);
        }while($iter !== null);
        
        return $ret;
    }
}

GObject::register_type('ProductSearchFrame');