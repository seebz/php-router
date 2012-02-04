<?php
/*
 * (c) Sébastien Corne <sebastien@seebz.net> (MIT License)
 * http://github.com/Seebz
 */




class Router {

	/**
	 * Différentes instances de `Route`.
	 *
	 * @see Route
	 * @var array
	 */
	static protected $_routes = array();


	/**
	 * Défini une nouvelle route.
	 *
	 * @see Route
	 * @param string $template
	 * @param array $params
	 * @return array
	 */
	static public function connect($template, array $params = array()) {
		if ($template instanceof Route) {
			static::$_routes[] = $template;
		} else {
			static::$_routes[] = new Route(compact('template', 'params'));
		}
		return static::$_routes;
	}


	/**
	 * Traite les différentes routes définies précédement via {@link Router\connect()} à la
	 * recherche de celle qui correspond au chemin `path` indiqué en argument. Retourne un tableau
	 * contenant les différents éléments récupérés à partir du chemin et des options de la route.
	 *
	 * @see Route::parse()
	 * @param string $path 
	 * @return array|false
	 */
	static public function process($path) {
		$routes = static::$_routes;
		foreach ($routes as $route) {
			if ($match = $route->parse($path)) {
				return $match;
			}
		}
		return false;
	}


	/**
	 * Tente de générer le chemin `path` correspondant aux critères spécifiés en argument, ceci sur
	 * base des différentes routes définies précédement via {@link Router\connect()}.
	 *
	 * @see Route::match()
	 * @param array $params
	 * @return string|false
	 */
	static public function match(array $params) {
		$routes = static::$_routes;
		foreach ($routes as $route) {
			if ($url = $route->match($params)) {
				return $url;
			}
		}
		return false;
	}

}




class Route {

	static public $patterns = array(
		'default'    => '[^/]+',
		'lang'       => '[a-z]{2}',
		'controller' => '[^/\.]+',
		'action'     => '[^/\.]+',
		'id'         => '[1-9][0-9]*',
		'page'       => '[1-9][0-9]*',
		'type'       => '[a-z]{2,4}',
	);


	protected $_template = '';
	protected $_pattern  = '';
	protected $_keys     = array();
	protected $_params   = array();


	public function __construct(array $config = array()) {
		if (isset($config['template'])) {
			$this->_template = $config['template'];
		}
		if (isset($config['pattern'])) {
			$this->_pattern = $config['pattern'];
		}
		if (isset($config['params'])) {
			$this->_params = $config['params'];
		}
	}


	/**
	 * @see Router\parse()
	 * @param string $path
	 * @return array|false
	 */
	public function parse($path) {
		if (empty($this->_pattern)) {
			$this->_compile();
		}
		if (preg_match("`^{$this->_pattern}$`", $path, $m)) {
			$params = $this->_params;
			foreach ($m as $key => $value) {
				if (intval($key) === $key || in_array($value, array('', '/'))) {
					continue;
				}
				$params[$key] = $value;
			}
			return $params;
		}
		return false;
	}


	/**
	 * @see Router\match()
	 * @param string $params
	 * @return string|false
	 */
	public function match(array $params) {
		// Vérification des clés
		array_diff(array_keys($params), array_keys($this->_params), $this->_keys);
		
		// Vérification des paramètres constants
		foreach($this->_params as $k => $v) {
			if (isset($params[$k]) && strpos($this->_template, '{:'. $k) === false) {
				if ($params[$k] == $v) {
					unset($params[$k]);
				} else {
					return false;
				}
			}
		}
		
		// Remplacement des termes par leurs valeurs
		$url = $this->_template;
		foreach($params as $k => $v) {
			$p1 = sprintf('{:%s'. '(?:[:][^}]+)?' .'}', $k);
			$p2 = '\(' .
				'(?U:[^)(]*)' .
					'([^)(\|]*' . $p1 . '[^)(\|]*)' .
				'(?U:[^)(]*)' .
				'\)[?]';
			
			$url = preg_replace("`({$p1})[?]`", '($1)?', $url);
			
			$old_url = $url;
			if (isset($this->_params[$k]) && $this->_params[$k] == $v
				&& preg_match("`{$p2}`", $url, $m)) {
				// Suppression d'un terme optionnel avec valeur par défaut
				$url = str_replace($m[0], '', $url);
			} else {
				$url = preg_replace("`{$p2}?`", '$1', $url);
				$url = preg_replace("`{$p1}?`", (string) $v, $url);
			}
			if ($url != $old_url) {
				unset($params[$k]);
			}
		}
		
		// Suppression des termes optionnels restant
		$url = preg_replace('`\(.+\)[?]`U', '', $url);
		$url = preg_replace('`(.)[?]`U', '', $url);
		
		// Tout a-t-il bien été traité ?
		if (!empty($params) || strpos($url, '{:') !== false) {
			return false;
		}
		return $url;
	}


	protected function _compile() {
		$this->_pattern = $this->_template;
		$this->_pattern = str_replace('.', '\.', $this->_pattern);

		preg_match_all('`{:([^:}]+)}|{:([^}]+):([^}]+)}`', $this->_pattern, $m);
		$this->_keys = array_filter(array_merge($m[1], $m[2]));
		
		for ($i = 0; $i < count($m[0]); $i++) {
			$n = !empty($m[1][$i]) ? $m[1][$i] : $m[2][$i];
			if (!empty($m[3][$i])) {
				$p = $m[3][$i];
			} elseif (isset(static::$patterns[$n])) {
				$p = static::$patterns[$n];
			} elseif (isset(static::$patterns['default'])) {
				$p = static::$patterns['default'];
			} else {
				$p = '[^/]+';
			}
			$this->_pattern = str_replace($m[0][$i], "(?P<{$n}>{$p})", $this->_pattern);
		}
	}

}



?>