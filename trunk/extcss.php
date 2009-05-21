<?php
/**
 * Ext CSS Converter
 * Copyright (c) 2009 Nikolai Gerasimov
 * Released under the MIT License (MIT-LICENSE.txt).
 * More information: http://www.extcss.com/
 */

class css_converter {

        function convert_file_to_css($file_name) {
                $text = file($file_name);
                $text = $this->css_main_convert($text);
                $temp = explode('.', $file_name);
                $css_file = fopen($temp[0].'.css', 'w');
                foreach($text as $string) {
                        fwrite($css_file, $string);
                        echo $string.'<br />';
                }
        }

        function css_main_convert($text) {
                $text = $this->css_standardizer($text);
                $text = $this->css_constants($text);
                $media = 0;
                foreach($text as $string) {
                        if(preg_match('/@media(.*){/', $string)) {
                                $media = 1;
                                break;
                        }
                }
                if($media) {
                        $text = $this->css_media_convert($text);
                } else {
                        $text = $this->css_block_nesting($text);
                }
                $text = $this->css_cleaning($text);
                return $text;
        }

        function css_media_convert($text) {
                $length = count($text);
                $z = 0;
                for($i = 0; $i < $length;) {
                        $newtext[] = $text[$i];
                        if(preg_match('/@media(.*){/', $text[$i])) {
                                while(1) {
                                        $i++;
                                        if(preg_match('/}/', $text[$i])) {
                                                if(! $z) {
                                                        $temp_text = $this->css_block_nesting($temp_text);
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
                foreach($text as $string) {
                        if(preg_match('/;/', $string)) {
                                $temp = explode(';', $string);
                                $newtext[] = $temp[0].';';
                                $newtext[] = $temp[1];
                        } else {
                                $newtext[] = $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/{/', $string)) {
                                $temp = explode('{', $string);
                                $newtext[] = $temp[0].'{';
                                $newtext[] = $temp[1];
                        } else {
                                $newtext[]= $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/}/', $string)) {
                                $temp = explode('}', $string);
                                $newtext[] = $temp[0];
                                $newtext[] = '}';
                                $newtext[] = $temp[1];
                        } else {
                                $newtext[] = $string;
                        }
                }
                $text = $newtext;
                $newtext = array();
                foreach($text as $string) {
                        if(preg_match('/[^\s]/', $string)) {
                                preg_match_all('/(\s*)(.*)(\s*)/', $string, $temp, PREG_SET_ORDER);
                                $newtext[] = $temp[0][2];
                        }
                }
                return $newtext;
        }

        function css_constants($text) {
                for($i = 0; $i < count($text); $i++) {
                        if(preg_match('/^\$vars/',$text[$i])) {
                                $begin_vars = $i;
                                $t = 0;
                                while(! preg_match('/}/', $text[$i])) {
                                        preg_match_all('/(\$\w*)=(.*);/U', $text[$i], $temp_vars, PREG_SET_ORDER);
                                        foreach($temp_vars as $temp_var) {
                                                $vars[$t][0] = $temp_var[1];
                                                $vars[$t][1] = $temp_var[2];
                                                $t++;
                                        }
                                        $i++;
                                }
                                for($t = 0; $t < $begin_vars; $t++) {
                                        $newtext[] = $text[$t];
                                }
                                for($t = ++$i; $t < count($text); $t++) {
                                        $newtext[] = $text[$t];
                                }
                                foreach($vars as $var) {
                                        foreach($newtext as $key => $str) {
                                                $newtext[$key] = preg_replace("/([:,]\s*)(\\$var[0])(\s*[;,])/U", "$1 $var[1]$3", $str);
                                        }
                                }
                                return $newtext;
                        }
                }
                return $text;
        }

        function css_block_nesting($text) {
                $length = count($text);
                $recompile = 0;
                $z = 0;
                for($j = 0; $j < $length;) {
                        if(preg_match_all('/(.*){/U', $text[$j], $temp_vars, PREG_SET_ORDER)) {
                                $newtext[] = $text[$j];
                                $names[0] = $temp_vars[0][1];
                                while(1) {
                                        $j++;
                                        if(preg_match_all('/(.*){/U', $text[$j], $temp_vars, PREG_SET_ORDER)) {
                                                if($recompile == 0) {
                                                        $names[1] = $temp_vars[0][1];
                                                        while(1) {
                                                                $j++;
                                                                if(preg_match('/{/', $text[$j])) {
                                                                        $z++;
                                                                }
                                                                if(preg_match('/}/', $text[$j])) {
                                                                        if(! $z) {
                                                                                $recompile = 1;
                                                                                break;
                                                                        } else {
                                                                                $z--;
                                                                        }
                                                                }
                                                                $temptext[] = $text[$j];
                                                        }
                                                } else {
                                                        $newtext[] = $text[$j];
                                                        $z++;
                                                }
                                        } else {
                                                $newtext[] = $text[$j];
                                                if(preg_match('/}/', $text[$j])) {
                                                        if(! $z) {
                                                                $j++;
                                                                if($recompile) {
                                                                        $newtext[] = $this->css_getnames($names).'{';
                                                                        foreach($temptext as $str) {
                                                                                $newtext[] = $str;
                                                                        }
                                                                        $newtext[] = '}';
                                                                        for($j; $j < $length; $j++) {
                                                                                $newtext[] = $text[$j];
                                                                        }
                                                                        return $this->css_block_nesting($newtext);
                                                                }
                                                                break;
                                                        } else {
                                                                $z--;
                                                        }
                                                }
                                        }
                                }
                        } else {
                                $newtext[] = $text[$j];
                                $j++;
                        }
                }
                return $newtext;
        }

        function css_getnames($names) {
                $temp_p = explode(',', $names[0]);
                $temp_s = explode(',', $names[1]);
                foreach($temp_s as $sname) {
                        foreach($temp_p as $pname) {
                                if(preg_match('/&:/', $sname)) {
                                        $sname = preg_replace('/&/', '', $sname);
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
                foreach($text as $key => $str) {
                        $text[$key] = preg_replace('/\s+/', ' ', $text[$key]);
                        $text[$key] = preg_replace('/\s,/U', ',', $text[$key]);
                }
                for($i = 0; $i < count($text);) {
                        if(preg_match('/{/', $text[$i]) && preg_match('/}/', $text[$i + 1])) {
                                $i += 2;
                        } else {
                                $newtext[] = $text[$i]."\n";
                                $i++;
                        }
                }
                return $newtext;
        }
}

function convert_to_css($name) {
        $css_converter = new css_converter;
        $css_converter -> convert_file_to_css($name);
}

convert_to_css("test.txt");
?>