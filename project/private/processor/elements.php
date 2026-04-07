<?php
    namespace processor\elements;

    //security
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }
    

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
            $class = __NAMESPACE__ . '\\' . $class_name;
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
        INDEX_RESET-> always start with index 0, 
        INDEX_STATIC-> only grab the element indicated by user by $quant times
        */
        const INDEX_START = 1;
        const INDEX_CONTINUE= 2;
        const INDEX_RESET= 3;
        const INDEX_STATIC= 4;

        protected static $allowed_flags = [self::INDEX_CONTINUE, self::INDEX_RESET, self::INDEX_STATIC];

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
                        $idx = new $class(self::$current_idx);
                        if(!is_int(self::$current_idx)) self::$current_idx = $idx; 
                        self::$current_idx++;
                    }
                    break;
                case self::INDEX_CONTINUE:
                    for($i=0; $i<self::$quant; $i++) {
                        new $class($class::$offset);
                        $class::$offset++;
                    }
                    break;
                case self::INDEX_RESET:
                    for($i=0; $i<self::$quant; $i++) new $class($i);
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

        final public static function render($element_selected,...$arg){
            try{
                $realname_class = self::get_class_name($element_selected);

                $arg_count = count($arg);

                switch($arg_count){
                    case 0: throw new \Exception("at least 2 argument is required to create element"); break;
                    case 1: self::$mode = self::INDEX_CONTINUE; break;
                    case 2: throw new \Exception("you must define the flag when argument is greater than 1"); break;
                    case 3: 
                        //$arg[2] is flag
                        //flag validation, since for some reason enum is not supported in php 7.4, instead use array
                        if(!in_array($arg[2], self::$allowed_flags)) throw new \Exception("invalid flag for element: {$element_selected}");
                        //valid flag, assign to mode
                        self::$mode = $arg[2];
                        //arg[1] is offset provided by user
                        self::$current_idx = self::$mode===self::INDEX_RESET? 0: $arg[1];
                        break;
                    default: throw new \Exception("too many arguments, at most 3 arguments is allowed"); break;
                }

                //if count = 1 then quant is second arg, else will be always third arg.
                $quant_pos = $arg_count===1? 0:1;
                //$arg[0] is quant, must be positive integer
                if(!is_int($arg[$quant_pos])||$arg[$quant_pos]<1) throw new \Exception("quant must be a positive integer");
                self::$quant = $arg[$quant_pos];
                
                //create element
                self::use_mode($realname_class);
            }
            catch(\Exception $e){
                echo '<pre class="server_error">'.$e.'</pre>';
            }
            return 0;  
        }
            
    }
    class CALCULATOR extends CUSTOM_ELEMENT{
        protected static $type = 'CALCULATOR';
        protected static $offset = 0;
        protected static $json_content = [];
        protected static $map = [];
        protected static $file_path = '';

        //element attributtes
        private $target = [];
        private $btn = [];
        private $class = '';
        private $targetboxid = '';
        private $calc_body = [];
        private $drag_el = [];
        private $targetID = '';

        public function __construct($current_idx){
            if($this::$json_content===[]) {$this::set_json_content();}

            if($this::$map===[])$this::$map = array_keys($this::$json_content);
            $key = is_int($current_idx) ? $this::$map[$current_idx] : $current_idx;
            
            $target = $this->target = $this::$json_content[$key]
            ?? throw new \Exception("target not find for ".self::$type." with index: {$current_idx}");

            $this->btn = $target['btn']??null;
            $this->class = key_empty($target['class']??'');
            //container
            $this->targetboxid = key_empty($target['targetboxid']??'','data-targetboxid');
            $this->drag_el = $target['draggable']??null;
            $this->calc_body = $target['body']??null;
            $this->targetID = $target['targetboxid']??'';

            //html render
            $this->create($current_idx);

            //grab index of current element in json, if current_idx is int, just use it, if is str, find the key in map and return the index
            return is_int($current_idx) ? $current_idx : array_keys($this::$map,$current_idx)[0];
        }

        public function create($current_idx){
            //tags to insert to button for open/close
            $new_tags_btn = ["data-openboxid"=>$this->targetID,"data-action"=>"open-calculator","data-type"=>"extendable"];
            $new_tags_drag = ["data-direction"=>"draggable","data-for"=>"draggable"];

            echo '<section class="'. $this->target['container_class'] .' '.$this->class.'" data-type="extendedBox" data-for="calculator"'.$this->targetboxid. '>';
                //button to open/close calculator, if btn exist
                if(is_array($this->btn)) self::create_children($this->btn,$new_tags_btn);
                //resizers
                RESIZER::create($this->target);
                self::create_children($this->drag_el,$new_tags_drag);
                //calculator body with buttons inside
                self::create_children($this->calc_body);
            echo '</section>';
        }
    }

    class SELECTOR extends CUSTOM_ELEMENT{
        protected static $type = 'SELECTOR';
        protected static $offset = 0;
        protected static $json_content = [];
        protected static $file_path = '';
        protected static $map = [];

        private $target = [];
        private $class = '';
        private $filterID = '';
        private $depencityID = '';
        private $itens = [];
        private $item_class = '';

        

        public function __construct($current_idx){
            if($this::$json_content===[]) $this::set_json_content();
            
            if($this::$map===[])$this::$map = array_keys($this::$json_content);
            $key = is_int($current_idx) ? $this::$map[$current_idx] : $current_idx;
            
            $target = $this->target = $this::$json_content[$key]
            ?? throw new \Exception("target not find for ".self::$type." with index: {$current_idx}");

            $this->class = key_empty($target['class']??'');
            //selector: item inside
            //data-filterid can not exist
            $this->filterID = key_empty($target['filterID']??'','data-filterid');
            $this->depencityID = key_empty($target['depencityID']??'','data-depencityid');
            $this->itens = $target['itens']??[];
            $this->item_class = key_empty($target['item_class']??'','class');

            //html render
            $this->create($current_idx);

            //grab index of current element in json, if current_idx is int, just use it, if is str, find the key in map and return the index
            return is_int($current_idx) ? $current_idx : array_keys($this::$map,$current_idx)[0];
        }


        private function create_item($item_selected){
            // if item not specificted or not find, create blank div
            if(empty($item_selected)){
                echo '<span></span>';
                return;
            }

            $depencity = key_empty($item_selected['depencitytype']??'','data-depencitytype');
            $class = key_empty($item_selected['class']??'','class');
            $type = key_empty($item_selected['type']??'','data-type');
            $order = key_empty($item_selected['order']??'','data-order');

            $innerElement = $item_selected['innerElements'];

            echo '<ul'.$class.$type.$depencity.$order.'>';
                self::create_children($innerElement);
            echo '</ul>';
        }

        public function create($current_idx){
                //container
                echo '<section class="'. $this->target['container_class'] .' '.$this->class.'">';
                    $default_selected = $this->target["default_selected"]??'';

                    echo '<button class="selector" data-action="open-selector" data-type="extendable"'.$this->filterID.'">';
                        //if depencityID exist, this must be blank div. only create when equal none or empty str
                        if($this->depencityID===''||$this->depencityID=='none') self::create_item($default_selected);
                        else {
                            //if depencity exist but default_selected has something. also avoid it is empty
                            if(is_array($default_selected) && !empty($default_selected)){
                                //add new item inside of array $item with array $default_selected
                                $this->itens[] = $default_selected;
                                $data[(string)$current_idx]['itens'][] = $default_selected;
                                //delete what is in
                                $data[(string)$current_idx]['default_selected'] = '';
                                //save new itens into json. JSON_PRETTY_PRINT->new line and space, JSON_UNESCAPED_UNICODE make á avaible in json
                                file_put_contents(self::$file_path[self::$type],json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                            }
                            echo '<span></span>';
                        }
                    echo '</button>';
                    
                    echo '<nav'.$this->item_class.' data-type="extendedBox"'.$this->depencityID.'>';
                        //loop through array
                        if(!empty($this->itens)&&is_array($this->itens)) {
                            foreach($this->itens as $i){
                                //create <ul> list
                                $this->create_item($i);
                            }};   
                    echo '</nav>';
                echo '</section>';
        }
    }

    abstract class RESIZER{
        private static function get_sides($setting_str,$len_setting){
            //attributting to: "top right bottom left"
            $setting = [];
            switch($len_setting){
                case 1:
                    for($i=0;$i<4;$i++) $setting[] = (int)$setting_str[0];
                    break;
                case 2:
                    $setting[0] = $setting[3] = (int)$setting_str[0];
                    $setting[1] = $setting[2] = (int)$setting_str[1];
                    break;
                case 3:
                    $setting[0] = (int)$setting_str[0];
                    $setting[1] = $setting[2] = (int)$setting_str[1];
                    $setting[3] = (int)$setting_str[2];
                    break;
                case 4:
                    for($i=0;$i<4;$i++) $setting[$i] = (int)$setting_str[$i];
                    break;
            }
            return $setting;
        }

        public static function create($target){
            $resizer_data = $target['resizer']??'';
            if(!$resizer_data) return;

            
            $pos_str = $resizer_data['position']??'';
            

            //skip all space and append in array
            $setting_str = preg_replace('/[^01]/', '', $pos_str);
            $len_setting = strlen($setting_str);

            if(empty($setting_str)) return;

            $setting = self::get_sides($setting_str,$len_setting);
            $dir = ['n','w','s','e'];

            $class = key_empty($resizer_data['class']??'');
            
            echo "<nav".key_empty($resizer_data['container_class']??'','class').">";
                // render resizers
                for($i=0;$i<4;$i++){
                    if($setting[$i]) {
                        echo '<div class="resizer'.$class.'" data-direction="'.$dir[$i].'">';
                        echo "</div>";
                    }
                }
                //corner
                for($i=0;$i<4;$i++){
                    for($j = $i+1 ; $j<4 ; $j++){
                        //skip opposite
                        if(abs($i-$j)==2) continue;

                        if($setting[$i]&&$setting[$j]){
                            $comb_dir = $dir[$i].$dir[$j];
                            echo '<div class="resizer'.$class.'" data-direction="'.$comb_dir.'">';
                            echo "</div>";
                        }
                    }
                }
            echo "</nav>";
        }
    }


function key_empty($key,$tag_to_add = null){
    $key = strtolower($key);
    if($key===''||$key==null||$key=='none') return '';
    else {
        if($tag_to_add) $key = ' ' . $tag_to_add . '="' . $key . '" ';
        return $key;
        }
}
?>