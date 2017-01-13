<?php

namespace Alfred\Workflows;

class Workflow
{
    protected $results = [];

    /**
     * Add a result to the workflow
     *
     * @return \Alfred\Workflows\Result
     */
    public function result()
    {
        $result = new Result;

        $this->results[] = $result;

        return $result;
    }

    /**
     * Sort the current results
     *
     * @param string $direction
     * @param string $property
     *
     * @return \Alfred\Workflows\Workflow
     */
    public function sortResults($direction = 'asc', $property = 'title')
    {
        usort($this->results, function ($a, $b) use ($direction, $property) {
            if ($direction === 'asc') {
                return $a->$property > $b->$property;
            }

            return $a->$property < $b->$property;
        });

        return $this;
    }

    /**
     * Filter current results (destructive)
     *
     * @param string $query
     * @param string $property
     *
     * @return \Alfred\Workflows\Workflow
     */
    public function filterResults($query, $property = 'title')
    {
        if ($query === null || trim($query) === '') {
            return $this;
        }

        $query = (string) $query;

        $this->results = array_filter($this->results, function ($result) use ($query, $property) {
                return strstr($result->$property, $query) !== false;
            });

        return $this;
    }

    /**
     * Output the results as JSON
     *
     * @return string
     */
    public function output()
    {
        $output = [
            'items' => array_map(function ($result) {
                            return $result->toArray();
                        }, array_values($this->results)),
        ];

        return json_encode($output);
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

		array_filter( $defaults, array( $this, 'empty_filter' ) );  // Filter out empty options from the array

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
}
