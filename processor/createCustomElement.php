<?php
    function create_children($innerElements){
        //this is just text
        if(!is_array($innerElements)) {echo ''.$innerElements;return;}
        //element
        else {
            foreach($innerElements as $el){
                $el_type = $el['element_type'];
                if($el_type) echo 'you must define type of element!';
                $innerText = key_empty($el['innerText']);
                //tags is object {"tag":"value for tag", "tag2":...}
                $tags = $el['tags'];

                $innerEl = $el['innerElements'];

                echo '<'.$el_type.' ';

                if(!empty($tags)) {
                    //loop throught array to echo all tags
                    foreach($tags as $k=>$v) echo key_empty($v,$k);
                };

                //check if has children
                create_children($innerEl);

                echo $innerText.'</'.$el_type.'>';
            }
        }
    }

    function key_empty($key,$tag_to_add = null){
        $key = strtolower($key);
        if(empty($key)||$key=='none') return '';
        else {
            if($tag_to_add) $key = ' ' . $tag_to_add . '="' . $key . '"';
            return $key;
            }
    }

    function create_item($item_selected){
        // if item not specificted or not find, create blank div
        if(empty($item_selected)){
            echo '<div></div>';
            return;
        }

        $depencity = key_empty($item_selected['depencitytype'],'data-depencitytype');
        $class = key_empty($item_selected['class'],'class');
        $type = key_empty($item_selected['type'],'data-type');
        $order = key_empty($item_selected['order'],'data-order');

        $innerElement = $item_selected['innerElements'];

        echo '<div'.$class.$type.$depencity.$order.'>';
            create_children($innerElement);
        echo '</div>';
    }

    function create_custom_element($element_selected,$quant){
        if(empty($element_selected)) return;
        else{
            $element_selected = strtolower($element_selected);
            //get data from folder data and make string get into json
            $data = json_decode(file_get_contents("./data/{$element_selected}.json"),true);
            //prevent data not find
            if($data){
                global $element_list;
                switch($element_selected){
                    case "selector":
                        //get which element started
                        $start_idx = $element_list[$element_selected];
                        //loop to get all information of selector, start with start_idx
                        for($count = 0;$count<$quant;$count++){
                            $start_idx += $count;
                            $target = $data["{$start_idx}"];

                            if($target){
                                $class = key_empty($target['class']);
                                //container
                                echo '<div class="'. $data['selector_container_class'] .' '.$class.'">';
                                    //selector: item inside
                                    //data-filterid can not exist
                                    $filterID = key_empty($target['data-filterid'],'class');
                                    echo '<div class="selector" data-action="open-selector" data-type="extendable"'.$filterID.'">';
                                        create_item($target["default_selected"]);
                                    echo '</div>';
                                echo '</div>';
                            ;}
                            else return;
                        }
                        break;
                }
            }
            else{
                echo "JSON Error:". json_last_error_msg();
            }
        }
    }
    
    //track for each element how many is created and to add attributte pre-written in data folder
    //key is name and value is how much created
    $element_list = ['selector'=>0, 'calculator'=>0];
    
?>
