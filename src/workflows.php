<?php
/**
* Name: 		Workflows
* Description: 	This PHP class object provides several useful functions for retrieving, parsing,
* 				and formatting data to be used with Alfred 2 Workflows.
* Author: 		David Ferguson (@jdfwarrior)
* Revised: 		2/9/2013
* Version:		0.2
*/
class Workflows {

	private $cache;
	private $data;
	private $bundle;
	private $path;
	private $home;
	private $results;

	/**
	* Description:
	* Class constructor function. Intializes all class variables. Accepts one optional parameter
	* of the workflow bundle id in the case that you want to specify a different bundle id. This
	* would adjust the output directories for storing data.
	*
	* @param $bundleid - optional bundle id if not found automatically
	* @return none
	*/
	function __construct( $bundleid=null )
	{
		$this->path = exec('pwd');
		$this->home = exec('printf $HOME');

		if ( file_exists( 'info.plist' ) ):
			$this->bundle = $this->get( 'bundleid', 'info.plist' );
		endif;

		if ( !is_null( $bundleid ) ):
			$this->bundle = $bundleid;
		endif;

		$this->cache = $this->home. "/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/".$this->bundle;
		$this->data  = $this->home. "/Library/Application Support/Alfred 2/Workflow Data/".$this->bundle;

		if ( !file_exists( $this->cache ) ):
			exec("mkdir '".$this->cache."'");
		endif;

		if ( !file_exists( $this->data ) ):
			exec("mkdir '".$this->data."'");
		endif;

		$this->results = array();
	}

	/**
	* Description:
	* Accepts no parameter and returns the value of the bundle id for the current workflow.
	* If no value is available, then false is returned.
	*
	* @param none
	* @return false if not available, bundle id value if available.
	*/
	public function bundle()
	{
		if ( is_null( $this->bundle ) ):
			return false;
		else:
			return $this->bundle;
		endif;
	}

	/**
	* Description:
	* Accepts no parameter and returns the value of the path to the cache directory for your
	* workflow if it is available. Returns false if the value isn't available.
	*
	* @param none
	* @return false if not available, path to the cache directory for your workflow if available.
	*/
	public function cache()
	{
		if ( is_null( $this->bundle ) ):
			return false;
		else:
			if ( is_null( $this->cache ) ):
				return false;
			else:
				return $this->cache;
			endif;
		endif;
	}

	/**
	* Description:
	* Accepts no parameter and returns the value of the path to the storage directory for your
	* workflow if it is available. Returns false if the value isn't available.
	*
	* @param none
	* @return false if not available, path to the storage directory for your workflow if available.
	*/
	public function data()
	{
		if ( is_null( $this->bundle ) ):
			return false;
		else:
			if ( is_null( $this->data ) ):
				return false;
			else:
				return $this->data;
			endif;
		endif;
	}

	/**
	* Description:
	* Accepts no parameter and returns the value of the path to the current directory for your
	* workflow if it is available. Returns false if the value isn't available.
	*
	* @param none
	* @return false if not available, path to the current directory for your workflow if available.
	*/
	public function path()
	{
		if ( is_null( $this->path ) ):
			return false;
		else:
			return $this->path;
		endif;
	}

	/**
	* Description:
	* Accepts no parameter and returns the value of the home path for the current user
	* Returns false if the value isn't available.
	*
	* @param none
	* @return false if not available, home path for the current user if available.
	*/
	public function home()
	{
		if ( is_null( $this->home ) ):
			return false;
		else:
			return $this->home;
		endif;
	}

	/**
	* Description:
	* Returns an array of available result items
	*
	* @param none
	* @return array - list of result items
	*/
	public function results()
	{
		return $this->results;
	}

	/**
	* Description:
	* Convert an associative array into XML format
	*
	* @param $a - An associative array to convert
	* @param $format - format of data being passed (json or array), defaults to array
	* @return - XML string representation of the array
	*/
	public function toxml( $a=null, $format='array' ) {

		if ( $format == 'json' ):
			$a = json_decode( $a, TRUE );
		endif;

		if ( is_null( $a ) && !empty( $this->results ) ):
			$a = $this->results;
		elseif ( is_null( $a ) && empty( $this->results ) ):
			return false;
		endif;

		$items = new SimpleXMLElement("<items></items>"); 	// Create new XML element

		foreach( $a as $b ):								// Lop through each object in the array
			$c = $items->addChild( 'item' );				// Add a new 'item' element for each object
			$c_keys = array_keys( $b );						// Grab all the keys for that item
			foreach( $c_keys as $key ):						// For each of those keys
				if ( $key == 'uid' ):
					$c->addAttribute( 'uid', $b[$key] );
				elseif ( $key == 'arg' ):
					$c->addAttribute( 'arg', $b[$key] );
				elseif ( $key == 'type' ):
					$c->addAttribute( 'type', $b[$key] );
				elseif ( $key == 'valid' ):
					if ( $b[$key] == 'yes' || $b[$key] == 'no' ):
						$c->addAttribute( 'valid', $b[$key] );
					endif;
				elseif ( $key == 'autocomplete' ):
					$c->addAttribute( 'autocomplete', $b[$key] );
				elseif ( $key == 'icon' ):
					if ( substr( $b[$key], 0, 9 ) == 'fileicon:' ):
						$val = substr( $b[$key], 9 );
						$c->$key = $val;
						$c->$key->addAttribute( 'type', 'fileicon' );
					elseif ( substr( $b[$key], 0, 9 ) == 'filetype:' ):
						$val = substr( $b[$key], 9 );
						$c->$key = $val;
						$c->$key->addAttribute( 'type', 'filetype' );
					else:
						$c->$key = $b[$key];
					endif;
				else:
					$c->$key = $b[$key];
				endif;
			endforeach;
		endforeach;

		return $items->asXML();								// Return XML string representation of the array

	}

	/**
	* Description:
	* Remove all items from an associative array that do not have a value
	*
	* @param $a - Associative array
	* @return bool
	*/
	private function empty_filter( $a ) {
		if ( $a == '' || $a == null ):						// if $a is empty or null
			return false;									// return false, else, return true
		else:
			return true;
		endif;
	}

	/**
	* Description:
	* Save values to a specified plist. If the first parameter is an associative
	* array, then the second parameter becomes the plist file to save to. If the
	* first parameter is string, then it is assumed that the first parameter is
	* the label, the second parameter is the value, and the third parameter is
	* the plist file to save the data to.
	*
	* @param $a - associative array of values to save
	* @param $b - the value of the setting
	* @param $c - the plist to save the values into
	* @return string - execution output
	*/
	public function set( $a=null, $b=null, $c=null )
	{
		if ( is_array( $a ) ):
			if ( file_exists( $b ) ):
				$b = $this->path."/".$b;
			elseif ( file_exists( $this->data."/".$b ) ):
				$b = $this->data."/".$b;
			elseif ( file_exists( $this->cache."/".$b ) ):
				$b = $this->cache."/".$b;
			else:
				$b = $this->data."/".$b;
			endif;
		else:
			if ( file_exists( $c ) ):
				$c = $this->path."/".$c;
			elseif ( file_exists( $this->data."/".$c ) ):
				$c = $this->data."/".$c;
			elseif ( file_exists( $this->cache."/".$c ) ):
				$c = $this->cache."/".$c;
			else:
				$c = $this->data."/".$c;
			endif;
		endif;

		if ( is_array( $a ) ):
			foreach( $a as $k => $v ):
				exec( 'defaults write "'. $b .'" '. $k .' "'. $v .'"');
			endforeach;
		else:
			exec( 'defaults write "'. $c .'" '. $a .' "'. $b .'"');
		endif;
	}

	/**
	* Description:
	* Read a value from the specified plist
	*
	* @param $a - the value to read
	* @param $b - plist to read the values from
	* @return bool false if not found, string if found
	*/
	public function get( $a, $b ) {

		if ( file_exists( $b ) ):
			$b = $this->path."/".$b;
		elseif ( file_exists( $this->data."/".$b ) ):
			$b = $this->data."/".$b;
		elseif ( file_exists( $this->cache."/".$b ) ):
			$b = $this->cache."/".$b;
		else:
			return false;
		endif;

		exec( 'defaults read "'. $b .'" '.$a, $out );	// Execute system call to read plist value

		if ( $out == "" ):
			return false;
		endif;

		$out = $out[0];
		return $out;											// Return item value
	}

	/**
	* Description:
	* Read data from a remote file/url, essentially a shortcut for curl
	*
	* @param $url - URL to request
	* @param $options - Array of curl options
	* @return result from curl_exec
	*/
	public function request( $url=null, $options=null )
	{
		if ( is_null( $url ) ):
			return false;
		endif;

		$defaults = array(									// Create a list of default curl options
			CURLOPT_RETURNTRANSFER => true,					// Returns the result as a string
			CURLOPT_URL => $url,							// Sets the url to request
			CURLOPT_FRESH_CONNECT => true
		);

		if ( $options ):
			foreach( $options as $k => $v ):
				$defaults[$k] = $v;
			endforeach;
		endif;

		array_filter( $defaults, 							// Filter out empty options from the array
			array( $this, 'empty_filter' ) );

		$ch  = curl_init();									// Init new curl object
		curl_setopt_array( $ch, $defaults );				// Set curl options
		$out = curl_exec( $ch );							// Request remote data
		$err = curl_error( $ch );
		curl_close( $ch );									// End curl request

		if ( $err ):
			return $err;
		else:
			return $out;
		endif;
	}

	/**
	* Description:
	* Allows searching the local hard drive using mdfind
	*
	* @param $query - search string
	* @return array - array of search results
	*/
	public function mdfind( $query )
	{
		exec('mdfind "'.$query.'"', $results);
		return $results;
	}

	/**
	* Description:
	* Accepts data and a string file name to store data to local file as cache
	*
	* @param array - data to save to file
	* @param file - filename to write the cache data to
	* @return none
	*/
	public function write( $a, $b )
	{
		if ( file_exists( $b ) ):
			$b = $this->path."/".$b;
		elseif ( file_exists( $this->data."/".$b ) ):
			$b = $this->data."/".$b;
		elseif ( file_exists( $this->cache."/".$b ) ):
			$b = $this->cache."/".$b;
		else:
			$b = $this->data."/".$b;
		endif;

		if ( is_array( $a ) ):
			$a = json_encode( $a );
			file_put_contents( $b, $a );
			return true;
		elseif ( is_string( $a ) ):
			file_put_contents( $b, $a );
			return true;
		else:
			return false;
		endif;
	}

	/**
	* Description:
	* Returns data from a local cache file
	*
	* @param file - filename to read the cache data from
	* @return false if the file cannot be found, the file data if found. If the file
	*			format is json encoded, then a json object is returned.
	*/
	public function read( $a )
	{
		if ( file_exists( $a ) ):
			$a = $this->path."/".$a;
		elseif ( file_exists( $this->data."/".$a ) ):
			$a = $this->data."/".$a;
		elseif ( file_exists( $this->cache."/".$a ) ):
			$a = $this->cache."/".$a;
		else:
			return false;
		endif;

		$out = file_get_contents( $a );
		if ( !is_null( json_decode( $out ) ) ):
			$out = json_decode( $out );
		endif;

		return $out;
	}

	/**
	* Description:
	* Helper function that just makes it easier to pass values into a function
	* and create an array result to be passed back to Alfred
	*
	* @param $uid - the uid of the result, should be unique
	* @param $arg - the argument that will be passed on
	* @param $title - The title of the result item
	* @param $sub - The subtitle text for the result item
	* @param $icon - the icon to use for the result item
	* @param $valid - sets whether the result item can be actioned
	* @param $auto - the autocomplete value for the result item
	* @return array - array item to be passed back to Alfred
	*/
	public function result( $uid, $arg, $title, $sub, $icon, $valid='yes', $auto=null, $type=null )
	{
		$temp = array(
			'uid' => $uid,
			'arg' => $arg,
			'title' => $title,
			'subtitle' => $sub,
			'icon' => $icon,
			'valid' => $valid,
			'autocomplete' => $auto,
			'type' => $type
		);

		if ( is_null( $type ) ):
			unset( $temp['type'] );
		endif;

		array_push( $this->results, $temp );

		return $temp;
	}

}