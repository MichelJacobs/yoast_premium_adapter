<?php
require dirname(__FILE__) . '/Puc/v4p10/Autoloader.php';
new Puc_v4p10_Autoloader();

require dirname(__FILE__) . '/Puc/v4p10/Factory.php';
require dirname(__FILE__) . '/Puc/v4/Factory.php';

//Register classes defined in this version with the factory.
foreach (
	array(
		'Plugin_UpdateChecker' => 'Puc_v4p10_Plugin_UpdateChecker',
		// 'Theme_UpdateChecker'  => 'Puc_v4p10_Theme_UpdateChecker',

	)
	as $pucGeneralClass => $pucVersionedClass
) {
	Puc_v4_Factory::addVersion($pucGeneralClass, $pucVersionedClass, '4.10');
	//Also add it to the minor-version factory in case the major-version factory
	//was already defined by another, older version of the update checker.
	Puc_v4p10_Factory::addVersion($pucGeneralClass, $pucVersionedClass, '4.10');
}

