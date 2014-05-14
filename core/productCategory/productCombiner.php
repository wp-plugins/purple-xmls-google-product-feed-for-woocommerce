<?php

class PProductEntry {
  public $taxonomyName;
  public $ProductID;
  public $Attributes = array();
  
  function GetAttributeList(){
    $result = '';
	foreach($this->Attributes as $ThisAttribute) {
	  $result .= $ThisAttribute . ', ';
	}
	return '['. $this->Name . '] ' . substr($result, 0, -2);
  }
}

class ProductCombiner {

  public $AttributeCategory = array();
  public $resultlist = array();

  function CreateAttributeCategories($list) {
    //iterate the list and build categories
	foreach($list as $listitem) {
	  //Try to find existing ProductAttribute
	  $x = $this->FindAttributeCategory($listitem);
	  if ($x == null) {

	    //Not found... make a new one
	    $x = new PProductEntry();
	    $x->taxonomyName = $listitem->taxonomy;
		$x->ProductID = $listitem->ID;
		$this->AttributeCategory[] = $x;
	  }
	  //Save the Attribute
	  $x->Attributes[] = $listitem->Attributes;
	  
	}
  }
  
  function CreateNewProductList($list, $childlist) {

    //iterate the list of products
	foreach($list as $listitem) {
	  if ($this->ExistsInChildList($listitem, $childlist)) {
	    $this->InsertAttributes($listitem, 0, '');
	  }

	}
	//Clean up the trailing comma from the attributes
	foreach($this->resultlist as $listitem) {
	  $listitem->Attributes = substr($listitem->Attributes, 0, -2);
  
	}

  }
  
  function ExistsInChildList($needle, $haystack) {
    $result = false;
	foreach($haystack as $x) {
	  if ($x->ID == $needle->ID) {
	    $result = true;
		break;
	  }
	}
	return $result;
  }
  
  function FindAttributeCategory($SearchTerm) {
    $Result = null;
    foreach($this->AttributeCategory as $ThisAttribute) {
	  if ($ThisAttribute->taxonomyName == $SearchTerm->taxonomy && $ThisAttribute->ProductID == $SearchTerm->ID) {
	    $Result = $ThisAttribute;
		break;
	  }
	}
	return $Result;
  }
  
  function InsertAttributes($product, $StartingIndex, $AttributeTrail) {

    //foreach($this->AttributeCategory as $key => $ThisAttribute) {
	for($i=$StartingIndex;$i<count($this->AttributeCategory);$i++){
	  $ThisAttribute = $this->AttributeCategory[$i];
	    if ($ThisAttribute->ProductID != $product->ID) {
		  continue;
		}
	  foreach($ThisAttribute->Attributes as $x) {

	  //Copy the product with just this attribute
	  $copyR = clone $product;
	  $copyR->Attributes = $AttributeTrail . $x . ', ';
	  $this->resultlist[] = $copyR;
	  
	  //if ($key >= $StartingIndex) {
	    $this->InsertAttributes($product, $i + 1, $AttributeTrail . $x . ', ');
	  //}
	  }
	}

  }

}


?>