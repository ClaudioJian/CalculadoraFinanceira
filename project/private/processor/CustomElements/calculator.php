<?php 
    namespace processor\CustomElements;

    //security
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }

    use processor\elements\CUSTOM_ELEMENT;
    use function processor\htmlhelper\key_empty;
    use processor\CustomElements\RESIZER;

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

?>