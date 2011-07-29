<?php
/*  Copyright 2011 Neill Russell & Inigo Media Ltd (email: neill@inigo.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
    Plugin Name: Thumbnail Create & Cache Plugin
    Plugin URI: http://inigo.net
    Description: creates thumbnails & caches them 
    Version: 0.1
    Author: Neill Russell
    Author URI: http://enru.co.uk
    License: GPL2 
*/
/*
    example usage: 

    <a href="/link/to/a/post"><img src="<?php echo inigo_thumb($image['guid'], 168, null, 'centre', true); ?>" alt="auto-generated thumbnail" /></a>
*/

class Inigo_Thumbnailer {
    var $cache = null;
    function __construct($dir) {
        $this->cache = get_template_directory() . '/'. $dir;
        if(!file_exists($this->cache)) {
            mkdir($this->cache, 0755, true);
        }
    }
    function cached($original, $width=168, $height=null, $position=null, $regenerate=false, $bg=array(255,255,255)) {
        $original_path = $this->path($original);
        if(is_null($height))  $height = $width;
        $this->width= $width;
        $this->height = $height;
        if(!in_array($position, array('left', 'centre', 'right', 'top', 'bottom', 'tc', 'tl','tr', 'bl','br'))) $position = 'centre';
        $this->position = $position;
        $this->regenerate = $regenerate;
        $this->bg = $bg;
        if(!file_exists($original_path)) return $original; 
        $thumb = $this->thumb($original_path);
        if($this->regenerate || !file_exists($thumb)) {
            $this->create($thumb, $original_path);
        }
        $parts = explode('/wp-content', $thumb);
        $uri = content_url() . $parts[1];
        return $uri;
    }
    function path($url) {
        $root = preg_replace('/themes$/', '', get_theme_root());
        $parts = explode('/wp-content', $url);
        return $root . $parts[1];
    }
    function thumb($original) {
        return $this->cache . '/' . basename($original);
    }
    function create($thumb, $original) {
        
        // calculate new proportional sizes

        list($width, $height, $image_type) = getimagesize($original);

        $proportionalWidth = $this->width;
        $proportionalHeight = $this->height;
        if($width / $height >= $this->width / $this->height) {
            $proportionalHeight = round(($this->width / $width) * $height);
        }
        else {
           $proportionalWidth = round(($this->height / $height) * $width);
        }

        $x = round(($this->width - $proportionalWidth)/2);
        $y = round(($this->height - $proportionalHeight)/2);
        switch($this->position) {
            case 'left': $x = 0; break;
            case 'right': $x = ($this->width - $proportionalWidth); break;
            case 'top': $y = 0; break;
            case 'bottom': $y = ($this->height - $proportionalHeight); break;
            case 'tc': $y = 0; break;
            case 'tl': 
                $x = 0;
                $y = 0; 
                break;
            case 'tr': 
                $x = ($this->width - $proportionalWidth); 
                $y = 0; 
                break;
            case 'bl': 
                $x = 0; 
                $y = ($this->height - $proportionalHeight); 
                break;
            case 'br': 
                $x = ($this->width - $proportionalWidth); 
                $y = ($this->height - $proportionalHeight); 
                break;
            default: break;
        }
        
        // create new resized image
        // imagecreatetruecolor gives better quality then plain imagecreate
        $resized = imagecreatetruecolor($this->width, $this->height); 

        // fill background
        $bgcolor = imagecolorallocate($resized, $this->bg[0], $this->bg[1], $this->bg[2]);
        imagefill($resized, 0, 0, $bgcolor);

        // create source image
        switch ($image_type) {
            case IMAGETYPE_GIF: 
                $source = imagecreatefromgif($original); 
                break;
            case IMAGETYPE_JPEG: 
                $source = imagecreatefromjpeg($original);  
                break;
            case IMAGETYPE_PNG: 
                $source = imagecreatefrompng($original); 
                break;
            default: return; break;
        }
        
        // resize
        if($source) {
            $success = imagecopyresampled(
                $resized, $source, 
                $x, $y, 0, 0,
                $proportionalWidth, $proportionalHeight, $width, $height);
        }
        
        if(is_file($thumb)) { unlink($thumb); }

        switch ($image_type) {
            case IMAGETYPE_GIF: imagegif($resized, $thumb); break;
            case IMAGETYPE_JPEG: imagejpeg($resized, $thumb, 100);  break; // best quality
            case IMAGETYPE_PNG: imagepng($resized, $thumb, 0); break; // no compression
            default: break;
        }
    }

}

function inigo_thumb($img_path, $width=168, $height=null, $position='centre', $regenerate=false, $bg=array(255,255,255)) {
    $t = new Inigo_Thumbnailer($cache='images/thumbnails');
    return $t->cached($img_path, $width, $height, $position, $regenerate, $bg);
}

