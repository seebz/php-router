<?php



// Homepage
Router::connect('/', array(
	'controller' => 'main',
	'action'     => 'homepage',
));



// Defaults
Router::connect('/{:controller}/({:action}/)?({:id}|{:param})?', array(
	'action' => 'index', // optionnal: default action
));



?>