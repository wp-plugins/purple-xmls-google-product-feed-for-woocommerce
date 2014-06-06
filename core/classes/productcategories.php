<?php

  /********************************************************************
  Version 2.0
    List (local) product categories
	By: Keneto 2014-05-11

  ********************************************************************/

class PProductCategories {

  function getList() {
    global $wpdb;
    $term_table = $wpdb->prefix . 'terms';
    $taxo_table = $wpdb->prefix . 'term_taxonomy';
    $sql = "SELECT taxo.term_id, taxo.parent, taxo.count, term.name FROM " . $taxo_table . " taxo
    		LEFT JOIN " . $term_table . " term ON taxo.term_id = term.term_id
    		WHERE taxo.taxonomy = 'product_cat'";
    // if (is_int($cateogry_id)) {
    //   $sql .= " AND taxo.parent = '".$cateogry_id."'";
    // }
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
    $opts = '<option value="0">----- Select a Category -----</option>';
    $opts .= '<option value="0">All Categories</option>';
    foreach ($mainCats as $mcat) {
        $opts .= '<option value="' . $mcat['cat']->term_id . '">' . $mcat['cat']->name . ' (' . $mcat['cat']->count . ') :::::::::::: (Main Category)::::::::::' . PHP_EOL;
        $opts .= '</option>' . PHP_EOL;
        if (isset($mcat['subs'])) {
            foreach ($mcat['subs'] as $scat) {
                $opts .= '		<option value="' . $scat->term_id . '"> - ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;
                $opts .= '		</option>' . PHP_EOL;
                if (isset($allCats[$scat->term_id]['subs'])) {
                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                        $opts .= '				<option value="' . $scat->term_id . '"> --- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;
                        $opts .= '				</option>' . PHP_EOL;
                        if (isset($allCats[$scat->term_id]['subs'])) {
                            foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                                $opts .= '					<option value="' . $scat->term_id . '"> ----- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;
                                $opts .= '					</option>' . PHP_EOL;
                                if (isset($allCats[$scat->term_id]['subs'])) {
                                    foreach ($allCats[$scat->term_id]['subs'] as $scat) {
                                        $opts .= '						<option value="' . $scat->term_id . '"> ------- ' . $scat->name . ' (' . $scat->count . ')' . PHP_EOL;
                                        $opts .= '						</option>' . PHP_EOL;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $opts;
  }
}

?>