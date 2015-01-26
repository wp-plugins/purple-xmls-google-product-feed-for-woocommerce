<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('', 'model')->localized_name = 'Style Number'; //black,gold,clear
	//Eyewear
	$this->addAttributeMapping('style_name', 'style_name')->localized_name = 'Style Name'; //black,gold,clear
	$this->addAttributeMapping('lens_color', 'lens_color')->localized_name = 'Lens Color'; //black,gold,clear
	$this->addAttributeMapping('lens_color_map', 'lens_color_map')->localized_name = 'Lens Color Map'; //valid value from worksheet
	$this->addAttributeMapping('magnification_strength', 'magnification_strength')->localized_name = 'Magnification Strength';
	$this->addAttributeMapping('frame_material_type', 'frame_material_type')->localized_name = 'Frame Material Type';
	$this->addAttributeMapping('lens_material_type', 'lens_material_type')->localized_name = 'Lens Material Type';
	$this->addAttributeMapping('item_shape', 'item_shape')->localized_name = 'Item Shape';
	$this->addAttributeMapping('polarization_type', 'polarization_type')->localized_name = 'Polarization Type'; //iridium
	$this->addAttributeMapping('lens_width', 'lens_width')->localized_name = 'Lens Width';
	$this->addAttributeMapping('eyewear_unit_of_measure', 'eyewear_unit_of_measure')->localized_name = 'Eyewear Unit Of Measure';
	//Eyewear, handbag, shoe accessory, shoes
	$this->addAttributeMapping('department_name', 'department_name')->localized_name = 'Department'; //ex: womens
	//Handbag, Shoes
	$this->addAttributeMapping('', 'color_name')->localized_name = 'Color'; //ex: Navy Blue
	for ($i = 1; $i <= 3; $i++)
		$this->addAttributeMapping("", "material_type$i", true)->localized_name = 'Material Fabric' . $i; //90% cotton/10% rayon
	//Shoes
	$this->addAttributeMapping('', 'size_name')->localized_name = 'Size'; //ex: X-Large, One Size

?>