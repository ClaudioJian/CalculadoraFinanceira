<?php
    function create_children($innerElements){
        global $error_msg;
        //this is just text
        if(!is_array($innerElements)) {echo $innerElements;return;}
        //element
        else {
            //avoid invalid elements insert and avoid void element
            $allowed_element = ['div', 'span', 'p', 'a', 'h1', 'h2', 'img', 'input', 'button'];
            $void_elements = ['img', 'input', 'br', 'hr'];

            foreach($innerElements as $el){
                $el_type = $el['element_type']??'';
                //check error
                if(empty($el_type)) {
                    echo 'you must define type of element!';
                    continue;
                    }

                //check if element is valid
                if(!in_array($el_type, $allowed_element)){
                    continue;
                }
                
                $innerText = key_empty($el['innerText']??'');
                //tags is object {"tag":"value for tag", "tag2":...}
                $tags = $el['tags']??[];
                $class = key_empty($el['class'],'class')??'';

                $innerEl = $el['innerElements']??'';

                echo '<'.$el_type.$class;
                    if(!empty($tags)) {
                    //loop throught array to echo all tags
                    foreach($tags as $k=>$v) echo key_empty($v,$k);
                    };

                //void element don't need innerText and child inside
                if(in_array($el_type, $void_elements)) echo ' />';
                else{
                    //normal element
                    //check if has children
                    echo ' >';
                    create_children($innerEl);
                echo  $innerText.'</'.$el_type.'>';
                }
            }
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

    function create_item($item_selected){
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
            create_children($innerElement);
        echo '</ul>';
    }

    function create_custom_element($element_selected,$quant){
        if(empty($element_selected)) return;
        else{
            $element_selected = strtolower($element_selected);
            $file_path = "./data/{$element_selected}.json";
            if(!file_exists($file_path)){
                echo 'path not find! path:'.$file_path;
                return;
            }

            //get data from folder data and make string get into json
            $data = json_decode(file_get_contents($file_path),true);
            //prevent data not find
            if($data){
                global $element_list;
                switch($element_selected){
                    case "selector":
                        //get which element started
                        $start_idx = $element_list[$element_selected];

                        //loop to get all information of selector, start with start_idx
                        for($count = 0; $count<$quant; $count++){
                            $current_idx = $start_idx + $count;
                            $target = $data[(string)$current_idx]??null;

                            if($target){
                                $class = key_empty($target['class']??'');
                                //container
                                echo '<section class="'. $data['selector_container_class'] .' '.$class.'">';
                                    //selector: item inside
                                    //data-filterid can not exist
                                    $filterID = key_empty($target['filterID']??'','data-filterid');
                                    $depencityID = key_empty($target['depencityID']??'','data-depencityid');
                                    
                                    $itens = $target['itens']??[];
                                    $default_selected = $target["default_selected"]??'';

                                    echo '<button class="selector" data-action="open-selector" data-type="extendable"'.$filterID.'">';
                                        //if depencityID exist, this must be blank div. only create when equal none or empty str
                                        if($depencityID===''||$depencityID=='none') create_item($default_selected);
                                        else {
                                            //if depencity exist but default_selected has something. also avoid it is empty
                                            if(is_array($default_selected) && !empty($default_selected)){
                                                //add new item inside of array $item with array $default_selected
                                                $itens[] = $default_selected;
                                                $data[(string)$current_idx]['itens'][] = $default_selected;
                                                //delete what is in
                                                $data[(string)$current_idx]['default_selected'] = '';
                                                //save new itens into json. JSON_PRETTY_PRINT->new line and space, JSON_UNESCAPED_UNICODE make á avaible in json
                                                file_put_contents($file_path,json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                            }
                                            echo '<span></span>';
                                        }
                                    echo '</button>';
                                    //itens
                                        $class = key_empty($data['item_class']??'','class');
                                    
                                    echo '<nav'.$class.' data-type="extendedBox"'.$depencityID.'>';
                                        //loop through array
                                        if(!empty($itens)&&is_array($itens)) {
                                            foreach($itens as $i){
                                                //create <ul> list
                                                create_item($i);
                                            }};   
                                    echo '</nav>';
                                echo '</section>';
                            ;}
                            else return;
                        }
                        $element_list[$element_selected] += $quant;
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