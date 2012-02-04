## Présentation

**php-router** est une librairie destinée à faciliter le routage des requêtes ainsi que la génération des uri de vos applications PHP.

#### Ce qu'elle permet de faire :

* Définir vos règles de routage
* Déterminer la route associée à une uri et retourner les règles de cette route (*processing*)
* Déterminer la route associée à des règles et retourner l'uri correspondante (*matching*)

#### Ce qu'elle ne fait pas (et ne fera jamais) :

* Tout le reste

## Utilisation

Vous trouverez ci-dessous un exemple basique d'utilisation de la librairie.  
A noter que les possibilités sont larges et que des exemples plus poussés seront disponible prochainement dans le wiki.

#### Configuration des règles

	// Homepage
	Router::connect('/', array(
		'controller' => 'main',
		'action'     => 'homepage',
	));
	
	// Default
	Router::connect('/{:controller}/({:action}/)?({:id}|{:param})?', array(
		'action' => 'index', // optionnal: default action
	));

#### Processing

	$rules = Router::process( '/' );
	// $rules => array('controller' => 'main', 'action' => 'homepage')

	$rules = Router::process( '/my-controller/my-action/42' );
	// $rules => array('controller' => 'my-controller', 'action' => 'my-action', 'id' => '42')

#### Matching

	$uri = Router::match( array('controller' => 'main', 'action' => 'homepage') );
	// $uri => '/'

	$uri = Router::match( array('controller' => 'my-controller', 'action' => 'my-action', 'id' => 42) );
	// $uri => '/my-controller/my-action/42'

## Documentation

*Une documentation sera disponible prochainement dans le wiki.*

## License

**php-router** est distribué sous les termes de la [licence MIT](http://www.opensource.org/licenses/mit-license.php).
Pour plus d'informations, référez-vous au fichier LICENSE.