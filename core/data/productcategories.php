<?php

  /********************************************************************
  Version 2.1
    List (local) product categories
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-11
  2014-06-06 Added Joomla calls

  ********************************************************************/

class PProductCategories {

  function __construct($restriction = null) {
    $this->getList();
	
	if (isset($restriction))
	  $this->isolateCategory($restriction);
  }
  
  private function idToCategory_internal($categories, $category_id) {
    $result = null;
	  if ($categories->id == $category_id) {
	    $result = $categories;
		return $result;
	  }
	foreach($categories->children as $child_category) {
	  $x = $this->idToCategory_internal($child_category, $category_id);
	  if ($x != null) {
		$result = $x;
		break;
	  }
	}
	return $result;
  }
  
  public function idToCategory($category_id) {
    foreach($this->categories as $this_category) {
	  $result = $this->idToCategory_internal($this_category, $category_id);
	  if ($result) return $result;
	}
	return null;
  }
  
  public function containsCategory($category_id) {
    return $this->idToCategory($category_id) != null;
  }

  private function doTallies($category, $depth = 0) {
    $result = 0;
	foreach($category->children as $child_category)
	  $result += $this->doTallies($child_category, $depth + 1);
	$category->tally += $result; //Save the subtally of direct_content + children
	$category->depth = $depth;
	$result = $category->tally;
	return $result;
  }
  
  private function interpretCategories($links) {
	//After pulling from db, we need do convert to categories
	
	//Prepare to convert categories into a hierarchical tree
	foreach($this->categories as $this_category) {
	  $this_category->children = array();
	}
	
	//Convert categories into the tree
	foreach($this->categories as $this_category) {
	  //find a parent id using links
	  $parent_id = -1;
	  foreach($links as $this_link) {
	    if ($this_category->id == $this_link->child_category) {
		  $parent_id = $this_link->parent_category;
		  break;
		}
	  }
	  //convert the parent id into an object and link
	  foreach($this->categories as $parent_category) {
	    if ($parent_category->id == $parent_id) {
		  $parent_category->children[] = $this_category;
		  $this_category->parent_category = $parent_category;
		  break;
		}
	  }
	}
	
	//Take all top-level categories (those with no parent) and initiate recursive tally
	foreach($this->categories as $this_category) {
	  if (!isset($this_category->parent_category))
	    $this->doTallies($this_category);
	}

  }
  
  public function isolateCategory($restriction)
  {
    $master_category = $this->idToCategory($restriction);
	if ($master_category) {
	  $this->categories = array($master_category);
	}
  }

  public function getList() {
    global $pfcore;
	$getListCall = 'getList' . $pfcore->callSuffix;
	return $this->$getListCall();
  }
  
  public function getListJ() {
  
    //Load the categories: id, title, tally-of-products
	$db = JFactory::getDBO();
	$query = 'SELECT a.virtuemart_category_id as id, b.category_name as title, count(c.virtuemart_category_id) as tally
      FROM #__virtuemart_categories a
	  LEFT JOIN #__virtuemart_categories_en_gb b 
	    ON a.virtuemart_category_id = b.virtuemart_category_id
	  LEFT JOIN #__virtuemart_product_categories c
	    ON a.virtuemart_category_id = c.virtuemart_category_id
	  GROUP BY a.virtuemart_category_id';
	$db->setQuery($query);
	$db->query();
	$this->categories = $db->loadObjectList();
	
	//Load the category-category-child links: parent_category, child_category
	$query = 'SELECT category_parent_id as parent_category, category_child_id as child_category
	  FROM #__virtuemart_category_categories';
	$db->setQuery($query);
	$db->query();
	$links = $db->loadObjectList();
	
	$this->interpretCategories($links);
	
  }

  /*The original code from prior versions. I just don't know what it does but it's wrong so
    I'm rewriting it
  public function getListW() {
    //The WordPress category fetch is from v2.6 and I have no idea how it does its job. 
	//I think it might be capped to 4 categories
	//Gross, no comments! -Keneto
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
  }*/

  public function getListW() {

		global $wpdb;

		//Fetch: id, title, tally-of-products
		$sql = "
			SELECT taxo.term_id as id, term.name as title, taxo.count as tally 
			FROM $wpdb->term_taxonomy taxo
			LEFT JOIN $wpdb->terms term ON taxo.term_id = term.term_id
			WHERE taxo.taxonomy = 'product_cat'";
		$source_categories = $wpdb->get_results($sql);

		//convert to objects
		$this->categories = array();
		foreach($source_categories as $a_source_category) {
			$this_category = new stdClass();
			$this_category->id = $a_source_category->id;
			$this_category->title = $a_source_category->title;
			$this_category->tally = $a_source_category->tally;
			$this->categories[] = $this_category;
		}

		//Fetch: parent_category, child_category
		$sql = "
			SELECT taxo.term_taxonomy_id as child_category, taxo.parent as parent_category 
			FROM $wpdb->term_taxonomy taxo
			WHERE taxo.taxonomy = 'product_cat'";
		$links = $wpdb->get_results($sql);
		
		$this->interpretCategories($links);
  }

	protected function indent($depth) {
	  if ($depth == 0)
		  return '';
		else
			return str_repeat('-', $depth);
	}

  public function getOptionList() {
	$opts = '<option value="0">----- Select a Category -----</option>';
	foreach($this->categories as $this_category)
	  $opts .= '<option value="' . $this_category->id . '">' . $this->indent($this_category->depth) . ' ' . $this_category->title . ' (' . $this_category->tally . ')';
	return $opts;
  }

}

?>