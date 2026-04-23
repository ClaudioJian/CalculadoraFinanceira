<?php
    namespace processor\CustomElements;
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }

    use function processor\htmlhelper\key_empty;
    use processor\elements\CUSTOM_ELEMENT;

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
                //container <section class="container-class additonal-class" id="">
                echo '<section class="' . $this->target['container_class'] . ' ' .$this->class.'" id="' .self::$map[$current_idx] .'">';
                    $default_selected = $this->target["default_selected"]??'';

                    echo '<button class="selector" data-action="open-selector" data-type="extendable"'.$this->filterID.'">';
                        //if depencityID exist, this must be blank div. only create when equal none or empty str
                        if($this->depencityID===''||$this->depencityID=='none') self::create_item($default_selected);
                        else {
                            //if depencity exist but default_selected has something. also avoid it is empty
                            if(is_array($default_selected) && !empty($default_selected)){
                                //add new item inside of array $item with array $default_selected
                                $this->itens[] = $default_selected;
                                $this::$json_content[(string)$current_idx]['itens'][] = $default_selected;
                                //delete what is in
                                $this::$json_content[(string)$current_idx]['default_selected'] = '';
                                //save new itens into json. JSON_PRETTY_PRINT->new line and space, JSON_UNESCAPED_UNICODE make á avaible in json
                                file_put_contents(self::$file_path,json_encode($this::$json_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                $this::set_json_content();
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
?>