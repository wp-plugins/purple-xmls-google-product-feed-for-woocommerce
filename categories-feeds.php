<?php
/**
 * Function to get xml file content of  categories feeds 
 */
function purple_feeds_page_getCategoriesFeeds() {
header ("Content-Type:text/xml");  
    global $wpdb;
    $term_table = $wpdb->prefix . 'terms';
    $taxo_table = $wpdb->prefix . 'term_taxonomy';
    $sql = "SELECT taxo.term_id, taxo.parent, taxo.count, term.name FROM " . $taxo_table . " taxo 
    		LEFT JOIN " . $term_table . " term ON taxo.term_id = term.term_id 
    		WHERE taxo.taxonomy = 'product_cat'";
    $cats = $wpdb->get_results($sql);

    $allCats = array();
    $mainCats = array();
    $subOneCats = array();
    $subTwoCats = array();
    $subThreeCats = array();
    $subFourCats = array();
    foreach ($cats as $cat) {
        if ($cat->parent == 0) {
            $mainCats[$cat->term_id]['cat'] = $cat;
        }
        $allCats[$cat->term_id]['cat'] = $cat;
        // echo '<br/>'.$cat->name;
    }
    foreach ($cats as $cat) {
        if ($cat->parent != 0 && isset($mainCats[$cat->parent])) {
            $mainCats[$cat->parent]['subs'][] = $cat;
            $subOneCats[$cat->term_id]['cat'] = $cat;
        } else if ($cat->parent != 0) {
            $allCats[$cat->parent]['subs'][] = $cat;
        }
    }
    foreach ($cats as $cat) {
        if ($cat->parent != 0 && isset($subOneCats[$cat->parent])) {
            $subOneCats[$cat->parent]['subs'][] = $cat;
            $subTwoCats[$cat->term_id]['cat'] = $cat;
        }
    }
    foreach ($cats as $cat) {
        if ($cat->parent != 0 && isset($subTwoCats[$cat->parent])) {
            $subTwoCats[$cat->parent]['subs'][] = $cat;
            $subThreeCats[$cat->term_id]['cat'] = $cat;
        }
    }
    foreach ($cats as $cat) {
        if ($cat->parent != 0 && isset($subThreeCats[$cat->parent])) {
            $subThreeCats[$cat->parent]['subs'][] = $cat;
            $subFourCats[$cat->term_id]['cat'] = $cat;
        }
    }

    echo '<?xml version="1.0"?>' . PHP_EOL;
    echo '<items>' . PHP_EOL;
    foreach ($mainCats as $mcat) {
        echo '<item>' . PHP_EOL;
        echo '	<id>' . $mcat['cat']->term_id . '</id>' . PHP_EOL;
        echo '	<title>' . $mcat['cat']->name . '</title>' . PHP_EOL;
        echo '	<count>' . $mcat['cat']->count . '</count>' . PHP_EOL;
        // echo '<br/>'.$mcat['cat']->name . ' ' . $mcat['cat']->count;
        if (isset($mcat['subs'])) {
            echo '	<subs>' . PHP_EOL;
            foreach ($mcat['subs'] as $scat) {
                echo '		<item>' . PHP_EOL;
                echo '			<id>' . $scat->term_id . '</id>' . PHP_EOL;
                echo '			<title>' . $scat->name . '</title>' . PHP_EOL;
                echo '			<count>' . $scat->count . '</count>' . PHP_EOL;
                // echo '<br/> --  '.$scat->name . ' ' . $scat->count;
                if (isset($allCats[$scat->term_id]['subs'])) {
                    echo '			<subs>' . PHP_EOL;
                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                        echo '				<item>' . PHP_EOL;
                        echo '					<id>' . $scat->term_id . '</id>' . PHP_EOL;
                        echo '					<title>' . $scat->name . '</title>' . PHP_EOL;
                        echo '					<count>' . $scat->count . '</count>' . PHP_EOL;
                        // echo '<br/> -----   '.$scat->name . ' ' . $scat->count;
                        if (isset($allCats[$scat->term_id]['subs'])) {
                            echo '					<subs>' . PHP_EOL;
                            foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                                echo '					<item>' . PHP_EOL;
                                echo '						<id>' . $scat->term_id . '</id>' . PHP_EOL;
                                echo '						<title>' . $scat->name . '</title>' . PHP_EOL;
                                echo '						<count>' . $scat->count . '</count>' . PHP_EOL;
                                // echo '<br/> --------   '.$scat->name . ' ' . $scat->count;
                                if (isset($allCats[$scat->term_id]['subs'])) {
                                    echo '						<subs>' . PHP_EOL;
                                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                                        echo '						<item>' . PHP_EOL;
                                        echo '							<id>' . $scat->term_id . '</id>' . PHP_EOL;
                                        echo '							<title>' . $scat->name . '</title>' . PHP_EOL;
                                        echo '							<count>' . $scat->count . '</count>' . PHP_EOL;
                                        // echo '<br/> ----------   '.$scat->name . ' ' . $scat->count;
                                        echo '						</item>' . PHP_EOL;
                                    }
                                    echo '						</subs>' . PHP_EOL;
                                }
                                echo '					</item>' . PHP_EOL;
                            }
                            echo '					</subs>' . PHP_EOL;
                        }
                        echo '				</item>' . PHP_EOL;
                    }
                    echo '			</subs>' . PHP_EOL;
                }
                echo '		</item>' . PHP_EOL;
            }
            echo '	</subs>' . PHP_EOL;
        }
        echo '</item>' . PHP_EOL;
    }


    echo '</items>' . PHP_EOL;


    exit;
    $categories = array();

    if (isset($category_id)) {
        $categories = isset($allCats[$category_id]) ? $allCats[$category_id] : array();
    }

    if (empty($categories)) {
        echo 'nothing found';
        die();
    }

    foreach ($categories as $category) {
        echo '<br/>' . $category->name;
    }
}

?>