<?php
    //avoid naming conflict
    namespace processor\classes;

    //security
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }

    //----------------------------------------------main logic------------------------------------------------------------------------
    abstract class CUSTOM_ELEMENT{
        protected $json_content;
        protected $offset = 0;
        protected $file_path;
        protected const DATA_PATH = PRIVATE_PATH . "data/";
        

        //force any class that extend this class must have these functions
        abstract public function create($quant);

        final public function get_json_content(){
            if(isset($this->json_content)) return $this->json_content;
            else false;
        }

        final public function set_json_content($type){
            if(isset($this->json_content)) return;

            $file = $this::DATA_PATH .$type.".json";

            if(!file_exists($file)){
                echo 'path not find! path:'.$file;
                $this->json_content = null;
                return;
            }
            $this->json_content = json_decode(file_get_contents($file),true);
            $this->file_path = $file;
        }
        final public function create_children($innerElements,$new_tags=[]){
            //this is just text
            if(!is_array($innerElements)) {echo $innerElements;return;}
            //element
            else {
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
                        $this->create_children($innerEl);
                    echo  $innerText.'</'.$el_type.'>';
                    }
                }
            }
        }

        final public function render($element_selected,$quant){
            if(empty($element_selected)) return;
            elseif(!is_int($quant)) echo "second argument for".__METHOD__."must be int: {$quant}";
            else{
                $element_selected = strtolower($element_selected);
                $file_path = PRIVATE_PATH . "data/{$element_selected}.json";

                if(!file_exists($file_path)){
                    echo 'path not find! path:'.$file_path;
                    return;
                }

                //get data from folder data and make string get into json
                $data = json_decode(file_get_contents($file_path),true);
                $realname_el_selected = __NAMESPACE__ . '\\' . $element_selected;
                //prevent data not find
                if(!$data){
                    echo "JSON Error:". json_last_error_msg();
                }
                elseif(!class_exists($realname_el_selected)){
                    echo "element not find: {$element_selected}";
                }
                else{
                    $realname_el_selected::create($quant);
                }
            }
        }

    }

    class CALCULATOR extends CUSTOM_ELEMENT{
        private $type = 'calculator';

        public function __construct()
        {
            $this->set_json_content($this->type);
        }
        public function create($quant){
            $data = $this->json_content;

            for($count = 0; $count<$quant; $this->offset += ++$count){
                $current_idx = $this->offset;
                $target = $data[(string)$current_idx]??null;

                if($target){
                    $class = key_empty($target['class']??'');
                    //container
                    $targetboxid = key_empty($target['targetboxid']??'','data-targetboxid');
                    //button to hide calculator
                    $btn = $target['btn'];
                    $drag_el = $target['draggable'];
                    $calc_body = $target['body'];

                    //tags to insert to button
                    $targetID = $target['targetboxid']??'';
                    $new_tags_btn = ["data-openboxid"=>$targetID,"data-action"=>"open-calculator","data-type"=>"extendable"];
                    $new_tags_drag = ["data-direction"=>"draggable","data-for"=>"draggable"];

                    echo '<section class="'. $target['container_class'] .' '.$class.'" data-type="extendedBox" data-for="calculator"'.$targetboxid. '>';
                        if(is_array($btn)) $this->create_children($btn,$new_tags_btn);
                        //resizers
                        RESIZER::create($target);
                        $this->create_children($drag_el,$new_tags_drag);
                        $this->create_children($calc_body);
                    echo '</section>';
                ;}
                else return;
            }
        }
    }

    class SELECTOR extends CUSTOM_ELEMENT{
        private $type = 'selector';

        public function __construct()
        {
            $this->set_json_content($this->type);
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
                $this->create_children($innerElement);
            echo '</ul>';
        }

        public function create($quant){
            $data = $this->json_content;

            //loop to get all information of selector, start with start_idx
            for($count = 0; $count<$quant; $count++){
                $current_idx = $this->offset;

                $target = $data[(string)$current_idx]??null;

                if($target){
                    $class = key_empty($target['class']??'');
                    //container
                    echo '<section class="'. $target['container_class'] .' '.$class.'">';
                        //selector: item inside
                        //data-filterid can not exist
                        $filterID = key_empty($target['filterID']??'','data-filterid');
                        $depencityID = key_empty($target['depencityID']??'','data-depencityid');
                        
                        $itens = $target['itens']??[];
                        $default_selected = $target["default_selected"]??'';

                        echo '<button class="selector" data-action="open-selector" data-type="extendable"'.$filterID.'">';
                            //if depencityID exist, this must be blank div. only create when equal none or empty str
                            if($depencityID===''||$depencityID=='none') $this->create_item($default_selected);
                            else {
                                //if depencity exist but default_selected has something. also avoid it is empty
                                if(is_array($default_selected) && !empty($default_selected)){
                                    //add new item inside of array $item with array $default_selected
                                    $itens[] = $default_selected;
                                    $data[(string)$current_idx]['itens'][] = $default_selected;
                                    //delete what is in
                                    $data[(string)$current_idx]['default_selected'] = '';
                                    //save new itens into json. JSON_PRETTY_PRINT->new line and space, JSON_UNESCAPED_UNICODE make á avaible in json
                                    file_put_contents($this->file_path,json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                }
                                echo '<span></span>';
                            }
                        echo '</button>';
                        //itens
                        $class = key_empty($target['item_class']??'','class');
                        
                        echo '<nav'.$class.' data-type="extendedBox"'.$depencityID.'>';
                            //loop through array
                            if(!empty($itens)&&is_array($itens)) {
                                foreach($itens as $i){
                                    //create <ul> list
                                    $this->create_item($i);
                                }};   
                        echo '</nav>';
                    echo '</section>';
                    $this->offset++;
                }
                else break;
            }
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