<?php
/**
 * Ext CSS Converter
 * Copyright (c) 2009 Nikolai Gerasimov
 * Released under the MIT License (MIT-LICENSE.txt).
 * More information: http://www.extcss.com/
 */
class css_converter {
        var $strings = array();
        var $ready_text = array();
        var $work_text = array();
        
        function convert_file_to_css($file_name) {
                if (file_exists($file_name)){
                        $text = file($file_name);
                        $text = $this->css_main_convert($text);
                        $file_name = preg_replace('/(ext-)(.*)/', "$2", $file_name);
                        $css_file = fopen($file_name, 'w');
                        foreach($text as $string) {
                                fwrite($css_file, $string);
                                echo $string.'<br />';
                        }
                } else {
                        echo "File \"$file_name\" not found!";
                }
        }

        function css_main_convert($text) {
                $text = $this->css_standardizer($text);
                $text = $this->css_constants($text);
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/@media(.*){/', $string) || preg_match('/@font-face\s*{/', $string)) {
                                $media = true;
                                break;
                        }
                }
                if($media) {
                        $text = $this->css_media_convert($text);
                } else {
                        while(!$triger){
                                $triger = $this->css_block_nesting($text);
                                $text = $this->work_text;
                        }
                        $text = $this->ready_text;
                        $this->ready_text = array();
                        $this->work_text = array();
                }
                $text = $this->css_cleaning($text);
                foreach($text as $string) {
                        if(preg_match_all('/@import_extcss \"(.*)\";/', $string, $temp, PREG_SET_ORDER)) {
                                $temp = $temp[0][1];
                                if (file_exists($temp)){
                                        $temp = file($temp);
                                        $newtext = array_merge($newtext, $this->css_main_convert($temp));
                                }else {
                                        $newtext[] = "/* File \"$temp\" not found! */";
                                }
                        } else {
                                $newtext[] = $string;
                        }
                }
                return $newtext;
        }

        function css_media_convert($text) {
                $length = count($text);
                $z = 0;
                for($i = 0; $i < $length;) {
                        $newtext[] = $text[$i];
                        if(preg_match('/@media(.*){/', $text[$i]) || preg_match('/@font-face\s*{/', $text[$i])) {
                                while(1) {
                                        $i++;
                                        if(preg_match('/}/', $text[$i])) {
                                                if(! $z) {
                                                        while(!$triger){
                                                                $triger = $this->css_block_nesting($temp_text);
                                                                $temp_text = $this->work_text;
                                                        }
                                                        $temp_text = $this->ready_text;
                                                        $this->ready_text = array();
                                                        $this->work_text = array();
                                                        $newtext = array_merge($newtext, $temp_text);
                                                        $newtext[] = '}';
                                                        $temp_text = array();
                                                        $i++;
                                                        break;
                                                } else {
                                                        $z--;
                                                        $temp_text[] = $text[$i];
                                                }
                                        } else {
                                                $temp_text[] = $text[$i];
                                                if(preg_match('/{/', $text[$i])) {
                                                        $z++;
                                                }
                                        }
                                }
                        } else {
                                $i++;
                        }
                }
                return $newtext;
        }
        
        function css_standardizer($text) {
                $i = 0;
                foreach($text as $key => $str) {
                        if(preg_match_all("/['|\"](.*)['|\"]/U", $str, $temp_vars, PREG_SET_ORDER)){
                                foreach($temp_vars as $vars){
                                        $this->strings[$i] = $vars[0];
                                        $temp = preg_quote($vars[1]);
                                        $text[$key] = preg_replace("/(['|\"])$temp(['|\"])/U", "temp_strings[$i]", $text[$key],1);
                                        $i++;
                                }
                        }
                        if(preg_match_all("/url\((.*)\)/U", $str, $temp_vars, PREG_SET_ORDER)){
                                foreach($temp_vars as $vars){
                                        $this->strings[$i] = $vars[0];
                                        $text[$key] = preg_replace("/url\((.*)\)/U", "temp_strings[$i]", $text[$key],1);
                                        $i++;
                                }
                        }
                }
                foreach($text as $key => $str) {
                        $text[$key] = preg_replace('/\/\*(.*)\*\//', '', $str);
                }
                $length = count($text);
                for($i = 0; $i < $length; $i++){
                        if(preg_match('/\/\*/', $text[$i])){
                                $newtext[] = preg_replace('/\/\*(.*)/', '', $text[$i]);
                                while($i < $length){
                                        $i++;
                                        if(preg_match('/\*\//', $text[$i])){
                                                $newtext[] = preg_replace('/(.*)\*\//', '', $text[$i]);
                                                break;
                                        }
                                }
                        }else{
                                $newtext[]=$text[$i];
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/;/', $string)) {
                                $temp = explode(';', $string);
                                $length = count($temp);
                                for($i = 0; $i < $length-1; $i++){
                                        $newtext[] = $temp[$i].';';
                                        }
                                $newtext[] = $temp[$length-1];
                        } else {
                                $newtext[] = $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/{/', $string)) {
                                $temp = explode('{', $string);
                                $length = count($temp);
                                for($i = 0; $i < $length-1; $i++){
                                        $newtext[] = $temp[$i].'{';
                                        }
                                $newtext[] = $temp[$length-1];
                        } else {
                                $newtext[]= $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/}/', $string)) {
                                $temp = explode('}', $string);
                                $length = count($temp);
                                for($i = 0; $i < $length-1; $i++){
                                        $newtext[] = $temp[$i];
                                        $newtext[] = '}';
                                        }
                                $newtext[] = $temp[$length-1];
                        } else {
                                $newtext[] = $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/[^\s]/', $string)) {
                                preg_match_all('/(\s*)(.*)(\s*)/', $string, $temp, PREG_SET_ORDER);
                                $newtext[] = rtrim($temp[0][2]);
                        }
                }
                $text = $newtext;
                $newtext = array();
                $length = count($text);
                for($i = 0; $i < $length; $i++){
                        if(preg_match('/,$/', $text[$i])){
                                $string = $text[$i];
                                while($i < $length){
                                        $i++;
                                        if(preg_match('/,$/', $text[$i])){
                                                $string.= ' '.$text[$i];
                                        }else{
                                                $newtext[] = $string.' '.$text[$i];
                                                break;
                                        }
                                }
                        }else{
                                $newtext[]=$text[$i];
                        }
                }
                return $newtext;
        }
        
        function css_constants($text) {
                for($i = 0; $i < count($text); $i++) {
                        if(preg_match('/^\$vars/',$text[$i])) {
                                $begin_vars = $i;
                                $vars = array();
                                $t = 0;
                                while(! preg_match('/}/', $text[$i])) {
                                        if(preg_match_all('/(\$\w*)\s*=\s*(.*);/U', $text[$i], $temp_vars, PREG_SET_ORDER)){
                                                foreach($temp_vars as $temp_var) {
                                                        $vars[$t][0] = $temp_var[1];
                                                        $vars[$t][1] = $temp_var[2];
                                                        $t++;
                                                }
                                        }
                                        $i++;
                                }
                                for($t = 0; $t < $begin_vars; $t++) {
                                        $newtext[] = $text[$t];
                                }
                                for($t = ++$i; $t < count($text); $t++) {
                                        $newtext[] = $text[$t];
                                }
                                if($t>0){
                                        foreach($vars as $var) {
                                                foreach($newtext as $key => $str) {
                                                        $newtext[$key] = preg_replace("/([:,\s]\s*)(\\$var[0])(\s*[;,\s])/U", "$1 $var[1]$3", $str);
                                                }
                                        }
                                }
                                return $newtext;
                        }
                }
                return $text;
        }

        function css_block_nesting($text) {
                $newtext = array();
                $length = count($text);
                $recompile = 0;
                $z = 0;
                for($j = 0; $j < $length;) {
                        if(preg_match_all('/(.*){/U', $text[$j], $temp_vars, PREG_SET_ORDER)) {
                                $names[0] = $temp_vars[0][1];
                                $j_begin = $j;
                                while(1) {
                                        $j++;
                                        if(preg_match('/{/', $text[$j]))$z++;
                                        if(preg_match('/}/', $text[$j])) {
                                                if(! $z) {
                                                        $j_end = $j;
                                                        break;
                                                } else {
                                                        $z--;
                                                }
                                        }

                                }
                                while($j>$j_begin) {
                                        $j--;
                                        if(preg_match('/}/', $text[$j])){
                                                $j_temp_end = $j;
                                                while(1){
                                                        $j--;
                                                        if(preg_match('/}/', $text[$j]))$z++;
                                                        if(preg_match_all('/(.*){/U', $text[$j], $temp_vars, PREG_SET_ORDER)) {
                                                                if(! $z) {
                                                                        $names[1] = $temp_vars[0][1];
                                                                        $recompile = 1;
                                                                        $j_temp_begin = $j;
                                                                        break;
                                                                } else {
                                                                        $z--;
                                                                }
                                                        }
                                                }
                                                break;
                                        }
                                }
                                if($recompile){
                                        for($j=$j_begin;$j<$j_temp_begin;$j++){
                                                $worktext[] = $text[$j];
                                        }
                                        for($j=$j_temp_end+1;$j<=$j_end;$j++){
                                                $worktext[] = $text[$j];
                                        }
                                        $worktext[] = $this->css_getnames($names).'{';
                                        for($j=$j_temp_begin+1;$j<=$j_temp_end;$j++){
                                                $worktext[] = $text[$j];
                                        }
                                        for($j=$j_end+1; $j < $length; $j++) {
                                                $worktext[] = $text[$j];
                                        }
                                        $this->ready_text = array_merge($this->ready_text, $newtext);
                                        $this->work_text = $worktext;
                                        return false;
                                }else{
                                        for($j=$j_begin;$j<=$j_end;$j++){
                                                $newtext[] = $text[$j];
                                        }
                                }
                        } else {
                                $newtext[] = $text[$j];
                                $j++;
                        }
                }
                $this->ready_text = array_merge($this->ready_text, $newtext);
                return true;
        }

        function css_getnames($names) {
                $temp_p = explode(',', $names[0]);
                $temp_s = explode(',', $names[1]);
                foreach($temp_s as $sname) {
                        foreach($temp_p as $pname) {
                                if(preg_match('/&:/', $sname)) {
                                        $sname = preg_replace('/(\s*)&:/', ':', $sname);
                                        $pname = rtrim($pname);
                                        $name .= $pname.$sname.', ';
                                } else {
                                        $name .= $pname.' '.$sname.', ';
                                }
                        }
                }
                preg_match_all('/(.*),/', $name, $temp, PREG_SET_ORDER);
                return $temp[0][1];
        }

        function css_cleaning($text) {
                for($i = 0; $i < count($text);) {
                        if(preg_match('/{/', $text[$i]) && preg_match('/}/', $text[$i + 1])) {
                                $i += 2;
                        } else {
                                $newtext[] = $text[$i];
                                $i++;
                        }
                }
                $i = 0;
                foreach($newtext as $key => $str) {
                        $newtext[$key] = preg_replace('/\s+/', ' ', $newtext[$key]);
                        $newtext[$key] = preg_replace('/\s,/', ',', $newtext[$key]);
                        if(preg_match("/temp_strings\[$i\]/", $newtext[$key])){
                                $temp = $this->strings[$i];
                                $newtext[$key] = preg_replace("/temp_strings\[$i\]/", "$temp", $newtext[$key]);
                                $i++;
                        }
                        $newtext[$key].="\n";
                }
                $this->strings = array();
                return $newtext;
        }
}

function convert_to_css($name) {
        $css_converter = new css_converter;
        $css_converter -> convert_file_to_css($name);
}
?>