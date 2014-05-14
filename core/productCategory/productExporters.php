<?php

class CategoryExport {
  public $feed_category;
}

class CategoryExportCSV extends CategoryExport {
  
  function DoExport($products, $aggregation) {
    echo 'ID, Title, Price, slug';
	if ($aggregation == 'C')
	  echo ', Attributes';
	echo PHP_EOL;
	foreach ($products as $prod) { 
	  $product = get_product($prod->ID);
	  if (!$this->feed_category->verifyProduct($product)) break;
	  echo $prod->ID . ',' . $prod->post_title . ',' . $product->price;
	  $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        if ($imgurl) {
            echo ',' . $imgurl;
        }
      echo $imgurl . ',' . $prod->post_name;
	  if ($aggregation == 'C')
	    echo ',' . $prod->Attributes;
	  echo PHP_EOL;
	}
  }
}

class CategoryExportTabbedTextFile extends CategoryExport {
  
  function DoExport($products, $aggregation) {
    echo 'ID' . "\t" . 'Title' . "\t" . 'Price' . "\t" . 'slug';
	if ($aggregation == 'C')
	  echo "\t" . 'Attributes';
	echo PHP_EOL;
	foreach ($products as $prod) { 
	  $product = get_product($prod->ID);
	  if (!$this->feed_category->verifyProduct($product)) break;
	  echo $prod->ID . "\t" . $prod->post_title . "\t" . $product->price;
	  $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        if ($imgurl) {
            echo "\t" . $imgurl;
        }
      echo $imgurl . "\t" . $prod->post_name;
	  if ($aggregation == 'C')
	    echo "\t" . $prod->Attributes;
	  echo PHP_EOL;
	}
  }
}

class CategoryExportXML extends CategoryExport {
  
  function DoExport($products, $aggregation) {
  
    echo '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
    echo '<items>' . PHP_EOL;
    foreach ($products as $prod) {
        $product = get_product($prod->ID);
		if (!$this->feed_category->verifyProduct($product)) break;
        echo '<item>' . PHP_EOL;
        echo '	<id>' . $prod->ID . '</id>' . PHP_EOL;
        echo '	<title>' . $prod->post_title . '</title>' . PHP_EOL;
        echo '	<price>' . $product->price . '</price>' . PHP_EOL;
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($prod->ID), 'small-feature');
        $imgurl = $thumb['0'];
        if ($imgurl) {
            echo '	<image_link>' . $imgurl . '</image_link>' . PHP_EOL;
        }
        echo '	<slug>' . $prod->post_name . '</slug>' . PHP_EOL;
		
		if ($aggregation == 'C') {
		        echo '	<attributes>' . $prod->Attributes . '</attributes>' . PHP_EOL;
		}

        echo '</item>' . PHP_EOL;
    }


    echo '</items>' . PHP_EOL;
  }

}



?>