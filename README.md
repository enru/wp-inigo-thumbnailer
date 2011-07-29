WordPress (WP) Thumbnailer from Inigo Media Ltd
===============================================

This is plugin from [Inigo Media Ltd](http://inigo.net/) provides a Template Tag inigo_thumb() that takes a image url hosted within your WP instance and generates a thumbnail for it.

e.g.

    <img src="<?php echo inigo_thumb($post['guid']); ?>" alt="example"/>

Full Example & Options
----------------------

    <?php 
    $src = inigo_thumb(
        $img_path, 
        $width=168, 
        $height=null, 
        $position='centre', 
        $regenerate=false, 
        $bg=array(255,255,255); 
    ?>
    
    <img src="<?php echo $src; ?>" alt="example"/>

* $img_path - The path of the image you want to create a thumbnail from. Assumes the image is in your WordPress instance under /wp-content. The thumbnail is created in /wp-content/[YOUR_ACTIVE_THEME]/images/thumbnails. This can be overridden. **Required**.

* $width - The width (in pixels) of the new thumbnail. Default is 168px.

* $height - The height  (in pixels) of the new thumbnail. Default is 168px.

* $position - The position of the sampled thumbnail within the new thumbnail dimensions. The proportions of the original image are kept. The sampled image can be placed: left, right, top, bottom, centre, tc (top centre), tl (top left), tr (top right), bl (bottom left), br (bottom right). The $position option can be one of: left, right, top, bottom, centre, tc, tl, tr, bl, br. Default is centre.

* $regenerate - A boolean true/false option to regenerate the thumbnail even if it exists. Useful if you change any of the above parameters. Default is false.

* $bg - The background colour of the thumbnail, an array of Red, Green, Blue (RGB) values. Default is 255,255,255 (white).
