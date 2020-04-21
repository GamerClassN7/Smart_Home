<?php
/**
* Mini Router
* @author github.com/Xinatorus
* https://github.com/Xinatorus/MiniRouter
*/
class Router{

	private $routes;
	private $defaultFunction;
	private $params;
	private $function;

	public function __construct(){
		$this->routes = [];
		$this->function = NULL;
		$this->defaultFunction = NULL;
		$this->params = [];
	}

	public function add($method, $pattern, $function){
		$this->routes[] = [
			'method' => $method,
			'pattern' => $pattern,
			'function' => $function,
		];
	}

	public function any($pattern, $function){
		$this->add(NULL, $pattern, $function);
	}

	public function get($pattern, $function){
		$this->add('GET', $pattern, $function);
	}

	public function post($pattern, $function){
		$this->add('POST', $pattern, $function);
	}

	public function put($pattern, $function){
		$this->add('PUT', $pattern, $function);
	}

	public function patch($pattern, $function){
		$this->add('PATCH', $pattern, $function);
	}

	public function delete($pattern, $function){
		$this->add('DELETE', $pattern, $function);
	}

	public function setDefault($function){
		$this->defaultFunction = $function;
	}

	public function run($method, $uri){
		if(!$this->matchRoute($method, $uri)){
			$this->function = $this->defaultFunction;
		}

		if($this->function !== NULL){
			if(is_string($this->function)){
				if(strpos($this->function, '@') !== false){
					list($class, $function) = explode('@', $this->function);
					$method = new ReflectionMethod($class, $function);
					if($method->isStatic()){
						call_user_func_array([$class, $function], $this->params);
					}else{
						call_user_func_array([new $class, $function], $this->params);
					}
				}else if(class_exists($this->function)){
					new $this->function(...$this->params);
				}else if (is_callable($this->function)) {
					call_user_func_array($this->function, $this->params);
				}
			}else if (is_callable($this->function)) {
				call_user_func_array($this->function, $this->params);
			}
		}
	}

	private function matchRoute($method, $uri){
		foreach($this->routes as $route){
			$patternRegex = $this->convertPatternToRegex($route['pattern']);
			$params = [];
			if(($route['method'] === NULL || $route['method'] === $method) && preg_match($patternRegex, $uri, $params)){
				array_shift($params);
				$this->params = $params;
				$this->function = $route['function'];
				return true;
			}
		}

		return false;
	}

	private function convertPatternToRegex($pattern){
		$regex = preg_replace('@{\w+}@', '([\w\d\-\.,]+)', $pattern);
		if ( substr($regex, -1) === '/' ) {
			$regex .= '?';
		}
		return '@^' . $regex . '$@';
	}
}
