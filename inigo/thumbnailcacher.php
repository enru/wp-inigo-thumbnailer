<?php
/*
	Plugin Name: Thumbnail Create & Cache Plugin
	Plugin URI: http://inigo.net
	Description: creates thumbnails & caches them 
	Version: 0.1
	Author: Neill Russell
	Author URI: http://enru.co.uk
	License: GPL2 
*/
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

class Inigo_Thumbnailer {
    var $cache = null;
	function __construct($dir) {
		$this->cache = get_template_directory() . '/'. $dir;
		if(!file_exists($this->cache)) {
			mkdir($this->cache, 0755, true);
		}
	}
	function cached($original) {
		$original_path = $this->path($original);
		if(!file_exists($original_path)) return $original; 
		$thumb = $this->thumb($original_path);
		if(!file_exists($thumb)) {
			$this->create($thumb, $original_path, 168, 168);
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
 	function create($thumb, $original, $newWidth, $newHeight) {
        
        // calculate new proportional sizes

		list($width, $height, $image_type) = getimagesize($original);

        $proportionalWidth = $newWidth;
        $proportionalHeight = $newHeight;
		if($width / $height >= $newWidth / $newHeight) {
        	$proportionalHeight = round(($newWidth / $width) * $height);
		}
		else {
        	$proportionalWidth = round(($newHeight / $height) * $width);
		}

        $x = round(($newWidth - $proportionalWidth)/2);
        $y = round(($newHeight - $proportionalHeight)/2);
        
        // create new resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight); // better quality then plain imagecreate

		/*
    	// Check if this image is PNG or GIF to preserve its transparency 
    	if(($image_type == IMAGETYPE_GIF) OR ($image_type==IMAGETYPE_PNG)) {
        	imagealphablending($tmp, false);
        	imagesavealpha($tmp,true);
        	$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        	imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
    	}
		*/

        // fill background
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);

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
        	$success = imagecopyresampled($resized, $source, $x, $y, 0, 0,$proportionalWidth, $proportionalHeight, $width, $height);
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

function inigo_thumb($img_path) {
	$t = new Inigo_Thumbnailer($cache='images/thumbnails');
	return $t->cached($img_path);
}

