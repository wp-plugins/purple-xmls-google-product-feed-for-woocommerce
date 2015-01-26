<?php

	/********************************************************************
	Wordpress Includes
	Version 2.0 (Even though it's not class-based)
	Since Joomla has a different way it likes to go about getting Includes,
	core needs to be a little more reliant on the CMS to set this up. Thus,
	WordPress has to carry more of the burden of setting up
		By: Keneto 2014-05-07

	********************************************************************/

require_once 'core/classes/md5.php';
require_once 'core/classes/cart-product-feed.php';
require_once 'core/classes/providerlist.php';
require_once 'core/data/attributedefaults.php';
require_once 'core/data/feedactivitylog.php';
require_once 'core/data/feedcore.php';
require_once 'core/data/productcategories.php';
require_once 'core/data/feedoverrides.php';
require_once 'core/data/productextra.php';
require_once 'core/data/productlist.php';
require_once 'core/data/shippingdata.php';
require_once 'core/data/taxationdata.php';
require_once 'core/registration.php';

?>