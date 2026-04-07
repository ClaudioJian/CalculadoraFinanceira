<?php
    namespace processor\elements;

    //security
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }
    
    require_once PRIVATE_PATH . "processor/htmlhelper.php";
    use function processor\htmlhelper\key_empty; 

   //----------------------------------------------main logic------------------------------------------------------------------------
    trait INNER_ELEMENT{
        final public static function create_children($innerElements,$new_tags=[]){
            //this is just text
            if(!is_array($innerElements)) {echo $innerElements;return;}
            //element
            //avoid invalid elements insert and avoid void element
            $allowed_element = ['div', 'span', 'p', 'a', 'h1', 'h2', 'img', 'input', 'button','output','section'];
            $void_elements = ['img', 'input', 'br', 'hr'];

            foreach($innerElements as $el){
                $el_type = $el['element_type']??'';
                //check error
                if(empty($el_type)) {
                    echo "you must enter element_type!{$el_type}";
                    }

                //check if element is valid
                if(!in_array($el_type, $allowed_element)){
                    echo '<pre style="position:absolute; padding:10px;background-color:rgb(255, 255, 255); border:2px black solid;color: rgb(169, 22, 22); font-size: 20px;">'."element invalid or not supported:{$el_type}".'</pre>';
                    return;
                }
                
                $innerText = key_empty($el['innerText']??'');
                //tags is object {"tag":"value for tag", "tag2":...}
                $tags = $el['tags']??[];
                $class = key_empty($el['class'],'class')??'';

                $innerEl = $el['innerElements']??'';

                
                echo '<'.$el_type.$class;
                    if(!empty($el)) {
                        //loop throught array to echo all tags
                        if(!empty($tags)&& is_array($tags)) foreach($tags as $k=>$v) echo key_empty($v,$k);
                        if(!empty($new_tags)&& is_array($new_tags)) foreach($new_tags as $t=>$v) echo key_empty($v,$t);
                    };

                //void element don't need innerText and child inside
                if(in_array($el_type, $void_elements)) echo ' />';
                else{
                    //normal element
                    //check if has children
                    echo ' >';
                    self::create_children($innerEl);
                    echo  $innerText.'</'.$el_type.'>';
                }
            }
        }
    }

    trait FIND_CLASS{
        static function get_class_name($class_name){
            $class_name = strtoupper($class_name);
            $class = 'processor\\CustomElements\\'.$class_name;
            if(!class_exists($class)){
                throw new \Exception("element not find: {$class_name}");
            }
            return $class;
        }
    }


    abstract class CUSTOM_ELEMENT{
        /*mode list: 
        INDEX_START-> start with index provided by user and create $quant times,
        INDEX_CONTINUE -> start with index stored in each class and add 1 every time create(not acessible by user, only for internal use), 
        INDEX_STATIC-> only grab the element indicated by user by $quant times
        */
        const INDEX_START = 1;
        const INDEX_CONTINUE= 2;
        const INDEX_STATIC= 3;

        protected static $allowed_flags = [self::INDEX_START, self::INDEX_STATIC];

        protected static $mode = self::INDEX_START;
        protected const DATA_PATH = PRIVATE_PATH . "data/";
        protected static $quant = 0;
        protected static $current_idx = -1;

        //placeholder
        protected static $json_content = null;
        protected static $file_path = null;
        protected static $type = null;
        protected static $offset = 0;

        //force any class that extend this class must have these functions
        abstract public function create($current_idx);

        //----------------------------method---------------------------------
        final static function use_mode($class){
            switch(self::$mode){
                case self::INDEX_START:
                    for($i=0; $i<self::$quant; $i++) {
                        //if is str, change to int, if is int, just use it
                        $obj = new $class(self::$current_idx);
                        if(!is_int($obj::$offset)) 
                        {
                            self::$current_idx = array_keys($obj::$map,$obj::$offset);
                        }
                        self::$current_idx++;
                    }
                    break;
                case self::INDEX_CONTINUE:
                    for($i=0; $i<self::$quant; $i++) {
                        new $class($class::$offset);
                        $class::$offset++;
                    }
                    break;
                case self::INDEX_STATIC:
                    $target_el = new $class(self::$current_idx);
                    for($i=1; $i<self::$quant; $i++) clone $target_el;
                    break;
            }
        }

        final public static function set_json_content(){
            $type = static::$type;
            $file = self::DATA_PATH . $type . ".json";

            if(!file_exists($file)){
                throw new \Exception("json file not find for element: {$type}");
            }

            static::$json_content = json_decode(file_get_contents($file),true);
            static::$file_path = $file;
            return 0;
        }

        use INNER_ELEMENT;
        use FIND_CLASS;

        final public static function get_key(){
            if(is_int(self::$current_idx)){
                array_keys(static::$json_content);
            }
        }

        final public static function reset_offset($element_selected){
            try{
                $class = self::get_class_name($element_selected);
                $class::$offset = 0;
            }
            catch(\Exception $e){
                echo '<pre class="server_error">'.$e.'</pre>';
            }
        }

        final public static function render($element_selected,$quant=null,$index=null,$flag=null){
            try{
                $realname_class = self::get_class_name($element_selected);
                $q = null;
                $i = null;
                $m = null;

                
                if(is_string($quant)){
                    //user uses render('element','index',quant=1)
                    $i = $quant;
                    $q = $index;
                    $m = $flag??self::INDEX_START;
                }
                //in case user pass flag but not index, use quant instead
                else if($flag!==null){
                    $i = ($index===null) ? $quant:$index;
                    $q = ($index===null) ? 1:$quant;
                    $m = $flag;
                }
                else{
                    $q = $quant;
                    $i = $index;
                }
                
                self::$quant = (int)($q??1);
                self::$current_idx = $i??$index;
                self::$mode = $m ?? self::INDEX_CONTINUE;

                //validation
                if(!is_int($q)||$q<0) throw new \Exception("quantity must be a positive integer not 0");
                if(is_int($i)&&$i<0) throw new \Exception("index must be a positive integer othrwise need be string");
                if($flag && !in_array($flag,self::$allowed_flags)) throw new \Exception("flag is invalid, allowed flag: ". implode(" | ", self::$allowed_flags));
                if(($m === self::INDEX_STATIC || $m === self::INDEX_START) && $i === null) throw new \Exception("index must be provided when use INDEX_STATIC or INDEX_START flag");
                
                //create element
                self::use_mode($realname_class);
            }
            catch(\Exception $e){
                echo '<pre class="server_error">'.$e.'</pre>';
            }
            return 0;  
        }
    }
?>