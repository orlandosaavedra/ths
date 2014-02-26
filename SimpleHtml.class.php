<?php
/**
 * This class is a wrapper for DOMNode to use in SimpleHTML class
 * so you can access html like $html->body->div->innerText;
 * @access protected
 * @uses DOMNode, DOMDocument, pcre functions
 * @version 1.0
 * @author Orlando Saavedra
 * @see SimpleHTML
 */
class SimpleHtmlElement //implements ArrayAccess
{
    const SEL_ELEM = 0;
    const SEL_ATTR = 1;
    const SEL_OP = 2;
    const SEL_ATTRVAL = 3;
    const SEL_SEL = 4;
    const SEL_SELVAL = 5;
    const SEL_ATTRS = 6;
    const SELECTOR = 7;
    
    /**
     *
     * @var string
     */
    public $tagName;
    /**
     * Contains the full innerHTML nodes and its childs
     * @var string
     */
    public $innerHTML;
    /**
     *  All the HTML object including its own tags
     * @var string
     */
    public $outerHTML;
    /**
     * The inner text or value of the HTML object
     * @var string
     */
    public $innerText;

    /**
     *
     * @var DOMNode
     */
    private $_dom;

    /*****************
     * MAGIC METHODS *
     *****************/
    
    /**
     *
     * @param mixed $domNode
     */
    protected function __construct($DOMNode)
    {
        if ($DOMNode instanceof DOMNode){
            $this->_dom = $DOMNode;
        }elseif(is_string($DOMNode)){
            $this->_dom = new DOMElement($DOMNode);
        }

        $this->tagName = $this->_dom->nodeName;
        $this->outerHTML = $this->__toString();
        preg_match('/<'.$this->tagName.'[^>]*>(.*)<\/'.$this->tagName.'>/s', $this->outerHTML, $match);
        $this->innerHTML = (key_exists(1, $match) ? $match[1] : null);
        $this->innerText = $this->_dom->nodeValue;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->_childs);
        unset($this->_dom);
    }

    /**
     * Outputs the object as string, this is an alternative way to access
     * $this->outerHTML
     * @return string
     */
    public function __toString()
    {
        //var_dump(debug_backtrace());
        if (!is_object($this->_dom)){
            throw new Exception("invalid SimpleHTML Object");
        }
        
        return $this->_dom->C14N();
    }

    /**
     *
     * @param string $function
     * @param array $params
     * @return SimpleHtmlElement
     */
    public function __call($function, $params)
    {
        //var_dump($params);
        //If the function has parameters and they are non-numerical
        if (count($params)>0 && is_nan($params[0])){
            if (strstr($params[0], '#')){ //id
                $id = substr($params[0], 1);
                $ele = $this->_dom->ownerDocument->getElementById(trim($id));
                if ($ele instanceof DOMElement){
                    return new self($ele);
                }else{
                    return false;
                }
            }elseif (strstr($params[0], '.')){ //class
                $class = substr($params[0], 1);
                $childs = $this->getChildElements();
                foreach ($childs as $child){
                    //echo "atributo class para $child->tagName es ". $child->getAttribute("class");
                    if ($child->getAttribute('class') == $class){
                        return $child;
                    }
                }

                return false;
            }
        }elseif(count($params)>0){ //The parameters are numerical
            $return = array();
            $childs = $this->_dom->childNodes;
            //iterate over the childs, to get what they are asking for
            for ($i=0;$i<$childs->length;++$i){
                if ($childs->item($i)->nodeType == XML_ELEMENT_NODE && $childs->item($i)->nodeName == $function){
                    $return[] = new self($childs->item($i));

                    /*
                    if ($return && is_array($return)){
                        $return[] = new self($childs->item($i));
                    }elseif($return){
                        $child = clone $return;
                        $return = array($child, new self($childs->item($i)));
                    }else{
                        $return = new self($childs->item($i));
                    }
                     *
                     */
                }
            }
            if ($params[0] === -1){
                //echo 'yes';
                return $return;
            }else{
                return $return[$params[0]];
            }
        }else{ //Function has no parameters
            $return = array();
            $childs = $this->_dom->childNodes;
            //iterate over the childs, to get what they are asking for
            for ($i=0;$i<$childs->length;++$i){
                if ($childs->item($i)->nodeType == XML_ELEMENT_NODE && $childs->item($i)->nodeName == $function){
                    return new self($childs->item($i));

                    /*
                    if ($return && is_array($return)){
                        $return[] = new self($childs->item($i));
                    }elseif($return){
                        $child = clone $return;
                        $return = array($child, new self($childs->item($i)));
                    }else{
                        $return = new self($childs->item($i));
                    }
                     *
                     */
                }
            }

        }

    }

    /**
     * //FIXME
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        //
    }

    /**
     * Magic method for accessing propertys
     * @param string $property
     * @return SimpleHtmlElement
     */
    public function __get($property)
    {
        //var_dump($property);
        return $this->$property(-1);
    }
/*
    public function offsetSet($offset, $value) {
        //$this->container[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
 *
 *
 */
    protected function getDOM()
    {
        return $this->_dom;
    }

    /******************
     * CUSTOM METHODS *
     ******************/
    public function appendChild(SimpleHtmlElement $element)
    {
        $this->_dom->appendChild($element->getDOM());
    }

    /**
     *
     * @param string $property Property name
     * @return string Propery value
     */
    public function getAttribute($property)
    {
        return $this->_dom->getAttribute($property);
    }

    /**
     *
     * @return array
     */
    public function getChildElements($recursive=false)
    {
        $return = array();
        $childs = $this->_dom->childNodes;

        if ($childs->length == 0){
            return $return;
        }
        
        for ($i=0; $i<$childs->length ;++$i){
            if ($childs->item($i)->nodeType == XML_ELEMENT_NODE){
                //echo $this->tagName . ' has subtag ' . $childs->item($i)->nodeName .' with type '. $childs->item($i)->nodeType . PHP_EOL;
                $return[] = $last = new self($childs->item($i));

                if ($recursive==true){
                    if ($last->childNodes()->length > 0){
                        $return = array_merge($return , $last->getChildElements(true));
                    }
                }
            }
        }

        return $return;
    }

    /**
     *
     * @param string $tag requested[s] tag[s]
     * @return array
     */
    public function getElementsByTagName($tag)
    {
        $index = $this->_dom->getElementsByTagName($tag);
        $return = array ();

        for ($i = 0; $i < $index->length; ++$i){
            $return[] = new self($index->item($i));
        }

        return $return;
    }

    public function getElementsByAttribute($attr, $value,$only_childs = false)
    {
        $store = array ();
        $childs = $this->getChildElements();

        foreach ($childs as $child){
            if ($child->getAttribute($attr) == $value){
                $store[] = $child;
            }

            if (false == $only_childs && ($news = $child->getElementsByAttribute($attr, $value))){
                $store = array_merge($store, $news);
            }
        }

        return (count($store)) ? $store : false;
    }

    public function childNodes()
    {
        return $this->_dom->childNodes;
    }

    public function find($exp, $recursive = true, &$box = null)
    {
        $parsed = $this->_parse($exp);

        $store = array();

        //check if ID has been provided
        if (key_exists(self::SELECTOR, $parsed) && $parsed[self::SELECTOR][self::SEL_SEL] == '#'){
            return array($this->getElementById($parsed[self::SELECTOR][self::SEL_SELVAL]));
        }

        //If tag is provided
        if (key_exists(self::SEL_ELEM, $parsed)){
            //echo 'tag  provided ';
            $tag = $parsed[self::SEL_ELEM];
           

            $elems = $this->getElementsByTagName($tag);
            $elems;
        }

        //check if selectors are used (.#)
        if (key_exists(self::SELECTOR, $parsed)){
            
            if (isset($elems)){
                foreach ($elems as $elem){
                    if ($elem->getAttribute('class') == $parsed[self::SELECTOR][self::SEL_SELVAL]){
                        $store[] = $elem;
                    }
                }

                $elems = $store;
            }else{
                $elems = $this->getElementsByAttribute('class', $parsed[self::SELECTOR][self::SEL_SELVAL]);
            }
        }

        //Check if attributes are required
        if (key_exists(self::SEL_ATTRS, $parsed)){
            //echo 'Attr are required'.PHP_EOL;
            $tmpstore = array ();
            //Foreach attribute equal to
            foreach ($parsed[self::SEL_ATTRS] as $attr => $op_val){
                if ($op_val[self::SEL_OP] == '='){

                    //If allready got elements
                    if (isset($elems) && count($elems)>0){
                       // echo '$elems exists value='.count($elems).PHP_EOL;
                        
                        foreach($elems as $ele){
                            //echo 'searching '.$op_val[self::SEL_ATTRVAL].PHP_EOL;
                            if ($ele->getAttribute($attr) == $op_val[self::SEL_ATTRVAL]){
                                //echo '$ele has the attr='.$op_val[self::SEL_ATTRVAL].PHP_EOL;
                                $tmpstore[] = $ele;
                            }
                        }
                        
                    }else{
                        if ($mer = $this->getElementsByAttribute($attr, $op_val[self::SEL_ATTRVAL])){
                            $tmpstore = array_merge($tmpstore,$mer);
                        }
                        
                    }
                }
            }

            $elems = $tmpstore;

            $remove = array ();
            foreach ($parsed[self::SEL_ATTRS] as $attr => $op_val){
                if ($op_val[self::SEL_OP] == '!='){
                    for($i=0;$i<count($elems); ++$i){
                        if ($elems[$i]->getAttribute($attr) == $op_val[self::SEL_ATTRVAL]){
                            array_slice($elems, $i);
                        }
                    }
                }
            }
        }

        if ($box != null){
            $box = $elems;
            return count($elems);
        }else{
            return $elems;
        }

        

        /*var_dump($elems);/*

        foreach ($elems as $elem){

        }*/
    }

    /**
     *
     * @param string $exp expression to search like the ones used in jquery
     * ej : 'div.content' or 'div#content' or '.content' or 'div[bgcolor=#ffffff]' etc
     * @param bool recursive if true will search all the way down, otherwise will
     * only search untill the first match then will continue with the next child of the parent
     * @param array $box optional box to store the found elements
     * @return bool|array if $box is passed will return the number of coincidences,
     * else will return an array containing the found objects
     * @example
     * $html->find('div#contents');
     * @example
     * $html = '<html><body><div id="hola"></div><ul><li class="def"></li><li class="def"></li></ul>';
     * $html .= '<table id="tablagris" class="muestra" bgcolor="#ffffff"><tr><td></td><td></td><td class="def">';
     * $html .= '<div class="def"></div></td></tr><tr id="row2"></tr>';
     * $html .= '</table><table class="ref" bgcolor="#ffffff"><table></table></table></body></html>';
     * $a = new SimpleHTML($html);
     * var_dump($a->find('table[bgcolor=#ffffff].ref', true));
     */
    public function oldfind($exp, $recursive = true, &$box = null)
    {

        if (null != $box){
            $return = false;
        }else{
            $box = array();
            $return = true;
        }

        $parsed = $this->_parseExp($exp); //preg_split('/([\W])/', $exp, -1, PREG_SPLIT_DELIM_CAPTURE);
        $elem = ($parsed[self::SEL_ELEM])? $parsed[self::SEL_ELEM] : false;
        switch ($parsed[self::SEL_SEL]){
            case '.':
                $sel = 'class';
                break;
            case '#':
                $sel = 'id';
                break;
            default :
                $sel = false;
                break;
        }

        $sel_value = $parsed[self::SEL_SELVAL];
        $attr = ($parsed[self::SEL_ATTR]) ? $parsed[self::SEL_ATTR] : false;
        $attr_value = ($attr) ? $parsed[self::SEL_ATTRVAL] : false;
        $op = ($attr) ? $parsed[self::SEL_OP] : false;

        $childs = $this->getChildElements();
        
        foreach ($childs as $child){
            
            $count = count($box);
            switch (true){
                case ($elem && $attr && $sel):
                    if  ($child->tagName == $elem && $child->getAttribute($sel) == $sel_value){
                        switch ($op){
                            case '=':
                                if ($child->getAttribute($attr) == $attr_value){
                                    $box[] = $child;
                                }
                                break;
                            case '!=':
                                if ($child->getAttribute($attr) != $attr_value){
                                    $box[] = $child;
                                }
                                break;
                        }

                    }
                    break;
                case ($elem && $attr && !$sel):
                    if ($child->tagName == $elem){
                        switch ($op){
                            case '=':
                                if ($child->getAttribute($attr) == $attr_value){
                                    $box[] = $child;
                                }
                                break;
                            case '!=':
                                if ($child->getAttribute($attr) != $attr_value){
                                    $box[] = $child;
                                }
                                break;
                        }
                    }
                    break;
                case ($elem && $sel && !$attr):
                    if ($child->tagName == $elem && $child->getAttribute($sel) == $sel_value){
                        $box[] = $child;
                    }
                    break;
                case ($sel && $attr && !$elem):
                    if ($child->getAttribute($sel) == $sel_value){
                        switch ($op){
                            case '=':
                                if ($child->getAttribute($attr) == $attr_value){
                                    $box[] = $child;
                                }
                                break;
                            case '!=':
                                if ($child->getAttribute($attr) != $attr_value){
                                    $box[] = $child;
                                }
                                break;
                        }
                    }
                    break;
                case ($elem && !$attr && !$sel):
                    if ($child->tagName == $elem){
                       $box[] = $child;
                    }
                    break;
                case ($attr && !$elem && !$sel):
                    switch ($op){
                        case '=':
                            if ($child->getAttribute($attr) == $attr_value){
                                $box[] = $child;
                            }
                            break;
                        case '!=':
                            if ($child->getAttribute($attr) != $attr_value){
                                $box[] = $child;
                            }
                    }

                    break;
                case ($sel && !$elem && !$attr):
                    if ($child->getAttribute($sel) == $sel_value){
                        $box[] = $child;
                    }
                    break;
            }

            if (count($box) > $count){
                if ($recursive){
                    $child->find($exp, $recursive, $box);
                }else{
                    continue;
                }
            }else{
                $child->find($exp, $recursive, $box);
            }
        }

        //return $box;
        
        if ($return){
            return $box;
        }else{
            return count($box);
        }
        
    }

    /**
     *
     * @param <type> $exp
     * @return array
     */
    private function _oldparse($exp)
    {
        $pattern = '/([\w]+)?(?:\[([\w]+)([=!]+)([^\]]*)\])?([.#])?(?(5)(.*))/';//[(\w)(=)([^\]]*)\]/';
        preg_match($pattern, $exp, $matches);
        //var_dump($matches);
        array_shift($matches);

        //Workaround to avoid E_WARNING and E_NOTICE
        for ($i = 0 ; $i <= 5 ; ++$i){
            $return[$i] = (key_exists($i, $matches))? $matches[$i] : null;
        }

        return $return;
    }

    private function _parse($exp)
    {
        $return = array ();

        if (preg_match('/(^[\w]+)/', $exp, $ele)){
            $return[self::SEL_ELEM] = $ele[1];
        }

        if (preg_match('/\[(.*)\]/', $exp, $attrs)){
            preg_match_all('/(\w+)([=!]{1,2})([^,]*)/', $attrs[1], $arr_attr);
            $attrs = array ();
            for($i=0;$i<count($arr_attr[0]);$i++){
                $attrs[$arr_attr[1][$i]] = array (
                    self::SEL_OP => $arr_attr[2][$i],
                    self::SEL_ATTRVAL => $arr_attr[3][$i]
                );
            }

            $return[self::SEL_ATTRS] = $attrs;

        }
        
        if (preg_match('/([#.])(.*)/', $exp, $op)){
            $return[self::SELECTOR] = array(
                self::SEL_SEL => $op[1],
                self::SEL_SELVAL => $op[2]
            );
        }

        return $return;
    }

    /**
     *
     * @param string $id Id of the form to get
     * @param string $name optional, if given id will be ignored
     * @return <type>
     */
    public function getForm($id, $name=null)
    {
        $by = 'id';
        $str = $id;
        if ($name != null){
            $by = 'name';
            $str = $name;
        }
        $form = $this->find("form[$by=$str]");

        $form = (count($form) > 0) ? $form[0]: false;
        
        if (!is_object($form)){
            return false;
        }

        $inputs = $form->find('input');

        $rform['id'] = $form->getAttribute('id');
        $rform['name'] = $form->getAttribute('name');
        $rform['method'] = $form->getAttribute('method');
        $rform['action'] = $form->getAttribute('action');

        $rinputs = array();

        foreach($inputs as $input){
            $rinputs[$input->getAttribute('name')] = $input->getAttribute('value');
        }

        $selects = $form->find('select');
        
        $rselects = array();

        foreach ($selects as $select){
            $rselects[$select->getAttribute('name')] = array();

            $options = $select->find('option');

            foreach ($options as $option){
                $rselects[$select->getAttribute('name')][$option->innerText] = $option->getAttribute('value');
            }
        }

        if (count($rselects)>0){
            $rinputs = array_merge($rinputs, $rselects);
        }

        $rform['data'] = $rinputs;

        return $rform;
    }

        /**
     *
     * @param string $id
     * @return SimpleHtmlElement
     */
    public function getElementById($id)
    {
        $object = $this->_dom->ownerDocument->getElementById($id);
        if (is_object($object)){
            return new SimpleHtmlElement($object);
        }else{
            $xpath = new DOMXPath($this->_dom->ownerDocument);
            $obj = $xpath->query("//*[@id='$id']")->item(0);
            if (is_object($obj)){
                return new SimpleHtmlElement($obj);
            }else{
                return false;
            }

        }
    }

}

/**
 * This class is an easy to access DOM for html
 *
 * @version 1.0
 * @author Orlando Saavedra
 *
 * @usage
 * accessing child nodes is simple as nesting objects like
 * $SimpleHTML->body->table->tr->td->div->span->innerText;
 * So what if body has 2 tables ? well the above code will return the first table
 * the one child element 0, in body , if you want to access the second table
 * you will have to do as follows:
 * $SimpleHTML->body->table(1)->tr->td->div->span->innerText;
 *
 * a short hand is accessing element by its "id" so, lets assume that de div has the
 * "content" id you could do this:
 * $SimpleHTML->div('#content')->span->innerText;
 * or
 * $SimpleHTML->getElementById("content")->span->innerText;
 *
 * note that accessing child elements in the form $obj->element('#id') will
 * return the element with that id in any namespace of the document so be 
 * carefull with this, this do not apply to element('.class') accesing this way
 * will only return the following childs having that class
 *
 * @example
 * $obj = SimpleHTML::new_from_file('http://www.example.com/');
 * echo $obj->body->p->innerText;
 * @example
 * $obj = SimpleHTML::new_from_file('http://www.example.com/');
 * echo $obj->body->p(1)->innerText;
 */
class SimpleHTML extends SimpleHtmlElement
{
    /**
     *
     * @var DOMDocument
     */
    private $_domDoc;

    /**
     *
     * @param string $html
     */
    public function __construct()
    {
        $this->_domDoc = new DOMDocument();
        $this->_domDoc->preserveWhiteSpace = false;
  
        if (func_num_args() == 0){
            $html = $this->_domDoc->createElement('html');
            $this->_domDoc->appendChild($html);
            parent::__construct($html);
        }elseif(is_string(func_get_arg(0)) && trim(func_get_arg(0)) != ''){
            @$this->_domDoc->loadHTML(func_get_arg(0));
            $htmlNodes = $this->_domDoc->getElementsByTagName('html');
            parent::__construct($htmlNodes->item(0));
        } elseif (!is_string(func_get_arg(0))){
            throw new Exception(__METHOD__ . " expects argument 1 to be a valid html document, ". gettype(func_get_arg(0)) ." given");
        }
    }

    /**
     *
     * @return <type> 
     */
    public function __toString()
    {
        return $this->_domDoc->saveHTML();
    }
    
    /**
     *
     * @param string $file path to file (accepts the same ones as file_get_contents())
     * @return SimpleHTML
     */
    public static function new_from_file($file)
    {
        $html = file_get_contents($file);
        $html = trim(str_replace("\r", '', $html));
        return new self($html);
    }

    /**
     *
     * @param string $element
     */
    public function createElement($element)
    {
        $ele = $this->_domDoc->createElement($element);
        return new SimpleHtmlElement($ele);
    }

    /**
     *
     * @param SimpleHtmlElement $element 
     */
    public function appendChild(SimpleHtmlElement $element)
    {
        $this->_domDoc->getElementsByTagName('html')->item(0)->appendChild($element->getDOM());
    }
}