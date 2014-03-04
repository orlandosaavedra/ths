<?php

//namespace THS;
/**
 * 
 */
class THSModel extends MySqli
{
    /**
     *
     * @var string
     */
    public static $host = null;
    /**
     *
     * @var string
     */
    public static $username = 'thehondastore';
    /**
     *
     * @var string
     */
    public static $password = 'UK5fJ2LX9dwbybuj';
    /**
     *
     * @var string
     */
    public static $dbname = null;
    
    
    
    public function __construct()
    {
        switch(null){
            case self::$host:
            case self::$username:
            case self::$password:
            case self::$dbname:
                throw new Exception('Usuario o Password incorrecto');
        }
        
        parent::__construct(self::$host, self::$username, self::$password, self::$dbname);

        switch ($this->connect_errno){
            case 1044:
            case 1045:
                throw new Exception('Acceso denegado a la base de datos');
                break;
            case 2002:
            case 2003:
                throw new Exception('No se pudo conectar a la base de datos');
                break;
            case 1049:
                throw new Exception('No existe la base de datos');
                break;
        }
    }
    
    /**
     * 
     * @param string $username
     * @param string $password
     * @return int employee id if sucess, false if login fails
     */
    public function employeeLogin($username, $password)
    {
        $password = sha1($password);
        $sql = "SELECT * FROM `employee` WHERE `username`='$username'"
                . " AND password='$password'";
        
        $result = $this->query($sql);
        
        if (is_object($result) && $result->num_rows==1){
            return $result->fetch_object()->id;
        }else{
            return false;
        }
    }
    
    
    public function getEmployee($e_id)
    {
        $sql = "SELECT * FROM `employee` WHERE `id`='$e_id'";
        $result = $this->query($sql);
        
        if (is_object($result) && $result->num_rows==1){
            return $result->fetch_object();
        }else{
            return null;
        }
    }
    
    public function createCompatibility($product_id, $description)
    {
        
    }
    
    /**
     * Returns the total stock for a product
     * @param integer $product_id
     * @return integer
     */
    public function getProductStock($product_id)
    {
        $sql = "SELECT `branch_id`,`stock` FROM `product_stock` WHERE `product_id`='$product_id'";
        $branches = $this->getBranches();
        $stock = array();
        
        foreach ($branches as $branch){
            $stock[$branch->id] = 0;
        }
        
        $qres = $this->query($sql);
        $total = 0;
        
        while ($obj = $qres->fetch_object()){
            $stock[$obj->branch_id] = $obj->stock;
            $total += (int)$obj->stock;
        }
        
        $stock[0] = $total;
                
        return $stock;
    }
    
    /**
     * 
     * @param type $id
     * @return Product
     */
    public function getProduct($id)
    {
        $qres = $this->query("SELECT * FROM `product` WHERE `id`='$id'");
        
        if ($qres->num_rows){
            $product = $qres->fetch_object('Product');
            $product->stock = $this->getProductStock($id);
            return $product;
        }else{
            return false;
        }
    }
    
    public function createProduct(Product $product)
    {
        $id = ($product->id)?: 'NULL';
        $category = ($product->category_id)?: 'NULL';
        $partnumber = ($product->partnumber)? "'".$product->partnumber."'": 'NULL';
        $cost = ($product->cost)?: 0;
        $price = ($product->price)?: 0;
        $description = $this->escape_string(strtoupper($product->description));
        
        $sql = "INSERT INTO `product` "
             . "(`id`, `partnumber`, `state`,"
             . " `description`, `cost`, `price`, `category_id`)"
             . " VALUES "
             . "({$id}, {$partnumber}, {$product->state},"
             . " '{$description}', {$cost}, {$price}, {$category->id})";
         echo $sql.PHP_EOL;       
        $this->query($sql);
                
        if ($this->errno){
            main::debug($this->error);
            return false;
        }else{
            $pid = $this->insert_id;
            /*
            $branches = $this->getBranches();
            
            foreach ($branches as $branch){
                $this->query("INSERT INTO `stock` VALUES ($id, {$branch->id}, 0)");
            }*/
            
            return $pid;
        }
    }
    
    /**
     * 
     * @param string $id
     * @return Vehicle
     */
    public function getVehicle($id)
    {
        $sql = "SELECT * FROM `vehicle` WHERE `id`='$id'";
        $res = $this->query($sql);
        if ($res->num_rows){
            return $res->fetch_object('Vehicle');
        }else{
            return false;
        }
    }
    
    public function getVehicleModels()
    {
        /**
         * Return variable
         */
        $ret = array();
        
        $result = $this->query ("SELECT DISTINCT `model` FROM `vehicle`");
        while ($obj = $result->fetch_object()){
            $ret[] = $obj->model;
        }
        
        return $ret;
    }
    
    /**
     * 
     * @param String $model
     * @return Array with the available years for such model
     */
    public function getVehicleModelYears($model=null)
    {
        $years = array();
        
        if ($model !== null){
            $res = $this->query("SELECT DISTINCT `year` FROM `vehicle` WHERE `model`='$model'");
        }else{
            $res = $this->query("SELECT DISTINCT `year` FROM `vehicle`");
        }

            while ($data = $res->fetch_object()){
                $years[] = $data->year;
            }

            return $years;
    }
   
    public function getVehicleModelVersions($model, $startyear=null, $endyear = null)
    {
        $versions  =array();
        $sql = "SELECT DISTINCT `version` FROM `vehicle` WHERE `model`='$model'";
        if ($startyear !== null){
            $sql .= " AND `year`>='{$startyear}'";
        }
        if ($endyear !== null){
            $sql .= " AND `year`<='{$endyear}'";
        }
        
        $res = $this->query($sql);
        while ($data = $res->fetch_object()){
            $versions[] = $data->version;
        }
        
        return $versions;
    }
    
    /**
     * 
     * @param type $model
     * @param type $startyear
     * @param type $endyear
     * @param type $version
     * @return type
     */
    public function getVehicleModelTransmissions($model, $startyear=null, $endyear=null, $version=null)
    {
        $sql = "SELECT DISTINCT `transmission` FROM `vehicle` WHERE `model`='$model'";
        if ($startyear !== null){
            $sql .= " AND `year`>='$startyear'";
        }
        
        if ($endyear !== null){
            $sql .= " AND `year`<='$endyear'";
        }
        
        if ($version !== null){
            $sql .= " AND `version`='$version'";
        }
        
        $qres = $this->query($sql);

        $trasmissions  = new SplFixedArray($qres->num_rows);
        
        for ($i=0;$i<$qres->num_rows;++$i){
            $trasmissions[$i] = $qres->fetch_object()->transmission;
        }
        
        return $trasmissions;
    }
    
    /**
     * 
     * @param type $string
     * @param type $constraint
     * @return \Product
     */
    public function searchProduct($string, ProductCompatibility $match=null)
    {
        $ret = array();
        
        $sql  = "SELECT `id` FROM `product` WHERE (`id` LIKE '%$string%'"
                . " OR `partnumber` LIKE '%$string%'"
                . " OR `description` LIKE '%$string%')";
        
        if ($match){
            
            $csql = " AND `id` IN (SELECT DISTINCT `product_id` FROM `product_compatibility`"
                    . " WHERE `vehicle_id` IN (SELECT `id` FROM `vehicle` WHERE ";
            
            $wcsql = array();

            if ($match->model){
                $wcsql[]= " `model`='{$match->model}' ";
            }
            
            if ($match->startyear){
                $wcsql[]= " `year`>='{$match->startyear}'";
            }
            
            if ($match->endyear){
                $wcsql[]= " `year`<='{$match->endyear}'";
            }
            
            if ($match->version){
                $wcsql []= " `version`='{$match->version}'";
            }
            
            if ($match->transmission){
                $wcsql[] = " `transmission`='{$match->transmission}'";
            }
            
            $csql .= implode(' AND ', $wcsql);
            $csql .= "))";
            
            if (count($wcsql)>0){
                $sql .= $csql;
            }

        }
        /*
        $sql = "SELECT id FROM product WHERE description LIKE '%{$string}%'";
        if ($search['model']!= null || $search['year'] != null){
            $sql .= " AND id IN (SELECT product_id FROM compatibility WHERE vehicle_id IN (SELECT id FROM vehicle ";
            $sql .= "WHERE ";
            if ($search['model'] != null && $search['year'] == null){
                $sql .=  "model='{$search['model']}'))";
            }else if ($search['year'] != null && $search['model'] == null){
                $sql .= "year={$search['year']}))";
            }else{
                $sql .=  "model='{$search['model']}' AND year={$search['year']}))";
            } 
        }*/
        
        $result = $this->query($sql);
        
        while ($obj = $result->fetch_object()){
            $ret[] = $obj->id;
        }
        
        return $ret;
    }
    
    public function getBranches()
    {
        $branches = array();
        $result = $this->query("SELECT * FROM branch");
        while ($branches[] = $result->fetch_object('Branch'));
        array_pop($branches);
        return $branches;
    }
    
    /**
     * 
     * @param integer $branch_id
     * @return Branch
     */
    public function getBranch($branch_id)
    {
        $sql = "SELECT * FROM `branch` WHERE `id`='$branch_id'";
        $res = $this->query($sql);
        
        if ($res){
            return $res->fetch_object('Branch');
        }else{
            return false;
        }
    }
    
    /**
     * 
     * @return \Category
     */
    public function getProductCategories()
    {

        $cat = array();
        $qres = $this->query("SELECT * FROM `product_category`");
        while ($cat[] = $qres->fetch_object('ProductCategory'));
        array_pop($cat);
        return $cat;
    }
    
    public function createProductCategory($name)
    {
        $name = strtoupper($name);
        if ($this->query("INSERT INTO `product_category` VALUES(NULL, '$name')")){
            return true;
        }else{
            return false;
        }
    }
    
    public function removeProductCategory($id)
    {
        $this->query("DELETE FROM `product_category` WHERE `id`='{$id}'");
    }
    
    /**
     * 
     * @param type $product_id
     * @param type $branch_id
     * @param type $stock
     * @return type
     */
    public function setProductStock($product_id, $branch_id, $stock)
    {
        $stock = ($stock<0)? 0 : $stock;
        
        $this->query("SELECT `stock` FROM `product_stock` WHERE `product_id`='$product_id' AND `branch_id`='$branch_id'");
        
        if ($this->affected_rows > 0){
        
            $sql = "UPDATE `product_stock` set `stock`='$stock'";
            $sql .= " WHERE `product_id`='$product_id'";
            $sql .= " AND `branch_id`='$branch_id'";
        
        }else{
            $sql = "INSERT INTO `product_stock` VALUES ('$product_id', '$branch_id', '$stock')";
        }
        
        $this->query($sql);
        
        if ($this->errno){
            Main::debug($this->error);
        }
        
        return $this->affected_rows;
    }
    
    public function setProductCompatibility($product_id, ProductCompatibility $compatibility)
    {
        $selectsql = array();
        $selectsql[]  = 'SELECT `id` FROM `vehicle`';
        
        if ($compatibility->model){
            $selectsql[] = "`model`='{$compatibility->model}'";
        }
        
        if ($compatibility->startyear){
            $selectsql[] = "`year`>='{$compatibility->startyear}'";
        }
        
        if ($compatibility->endyear){
            $selectsql[] = "`year`<='{$compatibility->endyear}'";
        }
        
        if ($compatibility->version){
            $selectsql[] = "`version`='{$compatibility->version}'";
        }
        
        if ($compatibility->transmission){
            $selectsql[] = "`transmission`='{$compatibility->transmission}'";
        }
        
        if (count($selectsql)>1){
            $ssql = $selectsql[0] . ' WHERE ';
            array_shift($selectsql);
            
            $ssql .= implode(" AND ", $selectsql);
        }else{
            $ssql = $selectsql[0];
        }
        
        Main::debug($ssql);
        
        $result = $this->query($ssql);
        
        while ($vehicle = $result->fetch_object()){
            $sql = "INSERT INTO `product_compatibility` VALUES ($product_id, {$vehicle->id})";
            Main::debug($sql);
            $this->query($sql);
        }
        
        return $result->num_rows;
    }
    
    public function getProductCompatibility($pid)
    {
        $compatibilities = array();
        
        $nsql = "SELECT `vehicle_id` FROM `product_compatibility` WHERE `product_id`='{$pid}'";
        
        $msql = "SELECT * FROM `vehicle` WHERE `id` IN ($nsql)";
        
        $res = $this->query($msql);
        
        while ($obj = $res->fetch_object('Vehicle')){
            $compatibility = new ProductCompatibility;
            $compatibility->model = $obj->model;
            $compatibility->startyear = $obj->year;
            $compatibility->endyear = $obj->year;
            $compatibility->version = $obj->version;
            $compatibility->transmission = $obj->transmission;
            $compatibilities[] = $compatibility;
        }
        
        return $compatibilities;
    }
    
    public function updateProduct(Product $product)
    {
        if ($product->id == null){
            throw new Exception('Can\'t update null');
        }
        
        $description = $this->escape_string(strtoupper($product->description));
        
        $category = ($product->category)?"'{$product->category}'": 'NULL';
        
        $sql = "UPDATE `product` SET "
                . " `partnumber`='{$product->partnumber}',"
                . " `state`='{$product->state}',"
                . " `description`='{$description}',"
                . " `cost`='{$product->cost}',"
                . " `price`='{$product->price}',"
                . " `category_id`=$category"
                . " WHERE `id`='{$product->id}'";
                
        $this->query($sql);
        
        if ($this->errno){
            Main::debug($this->error);
            return false;
        }
        
        if ($product->stock != null){
            foreach ($product->stock as $bid => $stock){
                $this->setProductStock($product->id, $bid, $stock);
            }
        }
        
        return true;
    }
    
    /**
     * @deprecated since version 1
     * @return \Product
     */
    public function getProducts()
    {
        $plist = $this->getProductList();
        $parray = new SplFixedArray($plist->getSize());
        
        for ($i=0;$i<$plist->getSize();++$i){
            $parray[$i] = Product::getFromId((int)$plist[$i]);
        }
        
        return $parray;
    }
    
    /**
     * Returns a list of product's id from database
     * @return SplFixedArray
     */
    public function getProductList($only_with_stock=false)
    {
        $sql = "SELECT `id` FROM `product`";
        
        if ($only_with_stock!==false){
            $sql .= " WHERE `id` IN ";
            $sql .= "(SELECT `product_id` FROM `product_stock` WHERE `stock`>0)";
        }
        
        $res = $this->query($sql);
        $productList = new SplFixedArray($res->num_rows);
        
        for ($i=0;$i<$res->num_rows;++$i){
            
            $productList[$i] = (int)$res->fetch_object()->id;
        }
        
        return $productList;
        
    }
    
    public function getVehicles()
    {
        $res = $this->query("SELECT * FROM `vehicle`");
        $ret = array();
        
        while($obj = $res->fetch_object()){
            $ret[] = $obj;
        }

        return $ret;
    }
    
    public function createVehicle($model, $year, $version, $transmission)
    {
        $model = strtoupper(trim($model));
        $year = trim($year);
        $version = strtoupper(trim($version));
        $transmission = strtoupper(trim($transmission));
        
        switch(null){
            case $model:
            case $year:
            case $version:
            case $transmission:
                return false;
                break;
        }
        
        $sql = "INSERT INTO `vehicle` "
                . "(`model`,`year`, `version`, `transmission`) "
                . "VALUES ('$model', '$year', '$version', '$transmission')";
        
        $result = $this->query($sql);
        
        if ($result){
            return $this->insert_id;
        }  else {
            return false;
        }
    }
    
    public function removeVehicle($vehicle_id)
    {
        $sql = "DELETE FROM `vehicle` WHERE `id`='$vehicle_id'";
        return $this->query($sql);
    }
    
    /**
     * 
     * @param int $employee_id
     * @param SalesCartFrame $productCart
     */
    public function registerSale($employee_id, $branch_id, $productCart)
    {
        
        $date = date('Y-m-d H:i:s');
        $total = $productCart->getTotals();
        $sql = "INSERT INTO sale (date, employee_id, branch_id, total) VALUES "
                . "('$date', '$employee_id', '$branch_id', {$total[SalesCartFrame::TOTAL_NET]})";
        $this->query($sql);
        Main::debug($this->error);
        
        $sale_id = $this->insert_id;
        
        $products = $productCart->getProducts();
        
        foreach ($products as $product){
            $sql = "INSERT INTO sale_product(sale_id, product_id, price)"
                    . " VALUES ({$sale_id}, {$product->id}, {$product->price})";
            $this->query($sql);
        }
    }
    
    public function updateProductCategory(ProductCategory $category)
    {
        $category->name = strtoupper($category->name);
        $sql = "UPDATE `product_category` SET `name`='{$category->name}'"
        . " WHERE `id`={$category->id} LIMIT 1";
        return $this->query($sql);
    }
    
    public function addProductCode($pid, $reference, $code)
    {
        $reference = $this->escape_string($reference);
        $code = $this->escape_string($code);
        $sql = "INSERT INTO `product_code` (`product_id`, `reference`, `code`)"
                . " VALUES ('$pid', '$reference', '$code')";
        return $this->query($sql);
    }
    
    public function getProductCodes($pid)
    {
        $sql = "SELECT * FROM `product_code` WHERE `product_id`='$pid'";
        $result = $this->query($sql);
        $codes = array();
        
        while ($obj = $result->fetch_object()){
            $codes[] = $obj;
        }
        
        return $codes;
    }
    
    public function deleteProductCodes($pid)
    {
        $sql = "DELETE FROM `product_code` WHERE `product_id`='$pid'";
        return $this->query($sql);
    }
}