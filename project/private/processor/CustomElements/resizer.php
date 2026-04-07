<?php 
    namespace processor\CustomElements;

    //security
    if(!defined('IS_ALLOWED')){
        http_response_code(403);
        exit('Direct access to this script is forbidden.');
    }

    use function processor\htmlhelper\key_empty;

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
?>