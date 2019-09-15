<?php
namespace Filmio;
/**
 * @package Filmio
 *
 */

/**
 * Object metadata
 *
 */
class InfoObject extends InfoRecords
{

	function __construct ( $params )
	{
		// Don't call the parent constructor if this is read-only
		foreach ( $params as $key => $value ) {
			$this->$key = $value;
		}
	}
}
?>
