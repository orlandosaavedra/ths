<?php
/**
 * Description of CartFrame
 *
 * @author orlando
 */

class SalesCartFrame extends GtkFrame
{
    /**
     *
     * @var GtkTreeView
     */
    protected $view;
    
    /**
     *
     * @var GtkEntry
     */
    protected $subtotal;
    /**
     *
     * @var GtkEntry
     */
    protected $discount;
    /**
     *
     * @var GtkEntry
     */
    protected $pdiscount;
    /**
     *
     * @var GtkEntry
     */
    protected $net;
    /**
     *
     * @var GtkEntry
     */
    protected $tax;
    
    /**
     *
     * @var GtkEntry
     */
    protected $total;
    
    public $__gsignals = array(
        'quote' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype)),
        'sell' => array(
            GObject::SIGNAL_RUN_LAST,
            GObject::TYPE_BOOLEAN,
            array(GObject::TYPE_LONG, GtkRequisition::gtype))
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->createLayout();
    }
    
    private function createSidePanelButtons()
    {
        $vbox = new GtkVbox();
        $delbtn = new GtkButton('Eliminar');
        $delbtn->set_size_request(170, 30);
        $delbtn->connect_simple('clicked', array($this, 'delete'));
        
        $vbox->pack_start($delbtn, false, false);
        
        $quotebtn = new GtkButton('Generar cotización');
        $quotebtn->connect_simple('clicked', array($this, 'emit'), 'quote');
        
        $vbox->pack_start($quotebtn, false, false);
        
        
        $checkbtn = new GtkButton('Realizar Venta');
        $checkbtn->connect_simple('clicked', array($this, 'emit'), 'sell');
        
        $vbox->pack_start($checkbtn, false, false);
        return $vbox;
    }
    
    private function createSidePanelEntries()
    {
        //Containers
        $main = new GtkHBox();
        $labelsColumn = new GtkVbox();
        $entriesColumn = new GtkVbox();
        
        $labels = array(
            'Subtotal', 
            'Descuento',
            'Neto',
            'IVA',
            'Total');
        
        foreach ($labels as $label){
            $wlabel = new GtkLabel($label.':');
            $wlabel->set_alignment(1, 1);
            $wlabel->set_size_request(-1, 32);
            $labelsColumn->pack_start($wlabel, false, false);
        }
        
        $entries =array(
            $this->subtotal = new GtkEntryNumeric(10),
            array(
                $this->pdiscount = new GtkEntryNumeric(2),
                new GtkLabel('%'),
                $this->discount = new GtkEntryNumeric(10)
                ),
            $this->net = new GtkEntryNumeric(10),
            $this->tax = new GtkEntryNumeric(10),
            $this->total = new GtkEntryNumeric(10)
        );
        
        foreach ($entries as $entry){
            if (is_array($entry)){
                $hbox = new GtkHBox;
                foreach ($entry as $w){
                    $hbox->pack_start($w);
                }
                $entriesColumn->pack_start($hbox, false, false);
                continue;
            }
            
            $entriesColumn->pack_start($entry, false, false);
        }
        
        $main->pack_start($labelsColumn, false, false);
        $main->pack_start($entriesColumn, false, false);
        $this->configureEntries();
        return $main;
        
    }
    
    private function configureEntries()
    {
        $this->subtotal->set_alignment(1);
        $this->subtotal->set_editable(false);
        $this->tax->set_alignment(1);
        $this->tax->set_editable(false);
        $this->tax->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->total->set_alignment(1);
        $this->total->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->total->connect_after('key-release-event', array($this, 'recalc'));
        $this->discount->set_alignment(1);
        $this->discount->set_editable(false);
        $this->pdiscount->set_alignment(1);
        $this->pdiscount->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->pdiscount->connect_after('key-release-event', array($this, 'recalc'));
        $this->net->connect('key-press-event', array('Main', 'restrictNumbersOnly'));
        $this->net->connect_after('key-release-event', array($this, 'recalc'));
        $this->net->set_alignment(1);
    }
    /**
     * Returns a vbox with all the side buttons
     */
    private function createSidePanel()
    {
        $buttons = $this->createSidePanelButtons();
        $entries = $this->createSidePanelEntries();
        $vbox = new GtkVbox();
        $vbox->pack_start($buttons, false, false);
        $vbox->pack_end($entries, false, false);
        return $vbox;
    }
    
    /**
     * 
     */
    private function createLayout()
    {
        $vbox = new GtkVBox;
        $hbox = new GtkHBox;
        
        $hbox->pack_start($this->_createListView());        
        $hbox->pack_start($this->createSidePanel(), false);
        
        $this->add($hbox);
    }
    
    /**
     * Returns all rows (raw data) contained in the cart
     * @return array
     */
    public function getRows()
    {
        $data = array();
        
        $iter = $this->view->get_model()->iter_children();
        
        while ($iter != null){
            /* Clear the array */
            $row = array();
            /* Values */
            for ($i=0;$i<$this->view->get_model()->get_n_columns();++$i){
                $row[] = $this->view->get_model()->get_value($iter, $i);
            }
            /* Add the iter */
            $row[] = $iter;
            $data[] =$row;
            $iter =$this->view->get_model()->iter_next($iter);
        }
        
        return $data;
    }
    
    /**
     * 
     * @return \Product
     */
    public function getProducts()
    {
        $rows = $this->getRows();
        $products = array();
        foreach ($rows as $row){
            $product = Product::getFromId($row[1]);
            $product->price = $row[4];
            $product->qty = $row[5];
            $products[] =$product;
        }
        
        return $products;
    }
    
    /**
     * Recalculates Total
     */
    public function recalc($changed=null)
    {
        if (null === $changed){
            $changed = $this->pdiscount;
        }
        
        $model = $this->view->get_model();
        $iter = $model->iter_children();
        $subtotal = 0;
        
        while ($iter !== null){
            $q = (int) $model->get_value($iter, 5);
            $subtotal += (int) $model->get_value($iter, 4) * $q;
            $iter = $model->iter_next($iter);
        }
        
        $this->subtotal->set_text("$subtotal");
        
        if ($changed === $this->pdiscount){
            $pdiscount = $this->pdiscount->get_text();
            $discount = (((int)$pdiscount/100)*(int)$subtotal);
            $this->discount->set_text($discount);
            $net = $subtotal - $discount;
            $tax = round($net*0.19, 0);
            $total = $net+$tax;
            $this->net->set_text($net);
            $this->tax->set_text($tax);
            $this->total->set_text($total);
        }else if ($changed === $this->total){
            $total = $this->total->get_text();
            $net = round($total / 1.19);
            $tax = $total-$net;
            $this->tax->set_text($tax);
            $this->net->set_text($net);
                        
            if ($subtotal < $net){
                
            }else if ($subtotal>$net){
                $discount = $subtotal-$net;
                $this->discount->set_text($discount);
                $pdiscount = round($discount/$subtotal * 100, 0);
                $this->pdiscount->set_text($pdiscount);
            }
            
        }else if ($changed === $this->net){
            $net = $this->net->get_text();
            $subtotal = $this->subtotal->get_text();
            $discount = $subtotal - $net;
            $this->discount->set_text($discount);
            $pdiscount = $discount / $subtotal * 100;
            $this->pdiscount->set_text(round($pdiscount, 0));
            $tax = round($net * 0.19, 0);
            $this->tax->set_text($tax);
            $total = $net + $tax;
            $this->total->set_text($total);
        }
        
        return false;
    }
    
    /**
     * 
     * @param array $data
     */
    public function append(Product $product)
    {
        /* Get the rows data to compare */
        $model = $this->view->get_model();
        $iter = $model->get_iter_first();
        
        while ($iter != null){
            $id = $model->get_value($iter, 0);
            if ($id === (integer)$product->id){
                $nitems = (int)$model->get_value($iter, 5);
                $model->set($iter, 5, ++$nitems);
                $this->recalc();
                return;
            }
            
            $iter = $model->iter_next($iter);
        }
        
        /* At this point no match was found */
        $data = array(
            $product->id,
            $product->partnumber,
            $product->description,
            ($product->state===Product::STATE_NEW)? 'Nuevo':'Usado',
            $product->price);
        $data[] = 1;
        $model->append($data);
        $this->recalc();
        return;
    }
    
    public function delete()
    {
        list($model, $iter) = $this->view->get_selection()->get_selected();
        
        if (!is_object($iter)){
            return false;
        }
        
        $model->remove($iter);
        $this->recalc();
    }
    
    /**
     * Creates the GtkTreeView and encapsulate it into a GtkScrolledWindow
     * @return GtkScrolledWindow
     */
    private function _createListView()
    {
        $scrwin = new GtkScrolledWindow();
        $scrwin->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
        
        $this->_model = new GtkListStore(
                GObject::TYPE_LONG,   // id
                GObject::TYPE_STRING, //PN
                GObject::TYPE_STRING, //descripcion
                GObject::TYPE_STRING, //Estado
                GObject::TYPE_LONG, //precio
                Gobject::TYPE_LONG //Cantidad
        );
        
        $this->view = new GtkTreeView($this->_model);
        
        foreach ($this->_createColumns() as $column){
            $this->view->append_column($column);
        }
        
        $scrwin->add($this->view);
        $this->view->set_grid_lines(Gtk::TREE_VIEW_GRID_LINES_BOTH);
        
        return $scrwin;
    }
    
    /**
     * Creates GtkTreeViewColumns
     * @return \GtkTreeViewColumn
     */
    private function _createColumns()
    {
        $columns = array();
        $columnHeaders = array(
            'Código',
            'Numero de parte',
            'Descripción',
            'Estado',
            'Precio',
            'Cantidad'
        );
        
        for ($i=0;$i<count($columnHeaders);++$i){
            $crt = new GtkCellRendererText();
            $column = new GtkTreeViewColumn($columnHeaders[$i], $crt, 'text', $i);
            $columns[] = $column;
            
            
            if ($columnHeaders[$i] == 'Descripción'){
                $column->set_expand(true);
            }
            
            if ($columnHeaders[$i] == 'Precio'){
                $crt->set_property('editable',true);
                $crt->connect('edited', array($this, 'edit'), $i);
            }
            
            if ($columnHeaders[$i] == 'Cantidad'){
                $crt->set_property('xalign', 0.5);
                $crt->set_property('editable',true);
                $crt->connect('edited', array($this, 'edit'), $i);
            }
        }
        
        return $columns;
    }
    
    public function edit($cell, $path, $new, $col)
    {
        $model = $this->view->get_model();
        $iter = $model->get_iter_from_string($path);
        $model->set($iter, $col, $new);
        $this->recalc();
    }
    
    public function clear()
    {
        $this->view->get_model()->clear();
        $this->subtotal->set_text('0');
        $this->discount->set_text('0');
        $this->pdiscount->set_text('0');
        $this->tax->set_text('0');
        $this->total->set_text('0');
        $this->net->set_text('0');
    }
    
    public function getTotal()
    {
        return $this->total->get_text();
    }
}

GObject::register_type('SalesCartFrame');