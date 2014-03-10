<?php

/**
 * Description of ProductCompatibility
 *
 * @author orlando
 */
class ProductCompatibility 
{
    const MATCH_ALL = 'TODOS';
    
    /**
     *
     * @var integer Product id INT(10)
     */
    public $product_id=null;
    /**
     *
     * @var string VARCHAR(20)
     */
    public $model=null;
    /**
     *
     * @var string VARCHAR(20)
     */
    public $version=null;
    /**
     *
     * @var string VARCHAR(20)
     */
    public $other=null;
    /**
     *
     * @var integer INT(4)
     */
    public $year_from=null;
    /**
     *
     * @var integer INT(4)
     */
    public $year_to=null;
    /**
     *
     * @var string VARCHAR(50)
     */
    public $obs=null;
}
