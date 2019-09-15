<?php
/**
 * @package Filmio
 *
 */

namespace Filmio;
/**
 * Filmio Site class
 *
 * Contains functions for getting details about the site directories and URLs.
 *
 */

class Site
{
	/**
	 * Constants
	 * CONFIG_LOCAL Local installation
	 * CONFIG_SUBDIR Subdirectory or multisite installation
	 * CONFIG_SUBDOMAIN Subdomain installation
	 */
	const CONFIG_LOCAL = 0;
	const CONFIG_SUBDIR = 1;
	const CONFIG_SUBDOMAIN = 2;

	/**
	 * @staticvar $config_path Filesystem path to config.php
	 * @staticvar $config_dir Multisite directory to config.php
	 * @staticvar $config_type Installation type (local, subdir, subdomain)
	 * @staticvar $scriptname the name of the currently executing script (index.php)
	 * @staticvar $filmio_url fully-qualified URL to the Filmio directory
	 */
	static $config_path;
	static $config_dir;
	static $config_type = Site::CONFIG_LOCAL;
	static $scriptname;
	static $filmio_url;
	static $config_urldir;

	/**
	 * Constructor
	 * This class should not be instantiated
	 */
	private function __construct()
	{
	}

	/**
	 * script_name is a helper function to determine the name of the script
	 * not all PHP installations return the same values for $_SERVER['SCRIPT_URL']
	 * and $_SERVER['SCRIPT_NAME']
	 */
	public static function script_name()
	{
		switch ( true ) {
			case isset( self::$scriptname ):
				break;
			case isset( $_SERVER['SCRIPT_NAME'] ):
				self::$scriptname = $_SERVER['SCRIPT_NAME'];
				break;
			case isset( $_SERVER['PHP_SELF'] ):
				self::$scriptname = $_SERVER['PHP_SELF'];
				break;
			default:
				Error::raise( _t( 'Could not determine script name.' ) );
				die();
		}
		return self::$scriptname;
	}

	/**
	 * is() returns a boolean value for whether the current site is the
	 * primary site, or a multi-site, as determined by the location of
	 * the config.php that is in use for this request.
	 * valid values are "main" and "primary" (synonymous) and "multi"
	 * @examples:
	 *	if ( Site::is('main') )
	 *	if ( Site::is('multi') )
	 * @param string $what The name of the boolean to test
	 * @return bool the result of the check
	 */
	public static function is( $what )
	{
		switch ( strtolower( $what ) ) {
			case 'main':
			case 'primary':
				if ( Site::$config_type == Site::CONFIG_LOCAL ) {
					return true;
				}
				else {
					return false;
				}
				break;
			case 'multi':
				if ( Site::$config_type != Site::CONFIG_LOCAL ) {
					return true;
				}
				else {
					return false;
				}
				break;
		}
	}

	/**
	 * get_url returns a fully-qualified URL
	 *	'host' returns http://www.Filmioproject.org
	 *	'filmio' returns http://www.Filmioproject.org/Filmio, if you
	 *		have Filmio installed into a /Filmio/ sub-directory
	 *  'site' returns http://www.Filmioproject.org/site if
	 *    you are installing with a subdirectory path
	 *	'user' returns one of the following:
	 *		http://www.Filmioproject.org/user
	 *		http://www.Filmioproject.org/user/sites/x.y.z
	 *	'theme' returns one of the following:
	 *		http://www.Filmioproject.org/user/themes/theme_name
	 *		http://www.Filmioproject.org/user/sites/x.y.z/themes/theme_name
	 *	'admin' returns http://www.Filmioproject.org/admin
	 *	'admin_theme' returns http://www.Filmioproject.org/system/admin
	 *  'login' returns http://www.Filmioproject.org/auth/login
	 *  'logout' returns http://www.Filmioproject.org/auth/logout
	 *	'system' returns http://www.Filmioproject.org/system
	 *	'vendor' returns http://www.Filmioproject.org/system/vendor
	 *	'scripts' returns http://www.Filmioproject.org/system/vendor
	 *	'3rdparty' returns http://www.Filmioproject.org/3rdparty
	 *     if /3rdparty does not exists, /system/vendor will be returned
	 *	'hostname' returns www.Filmioproject.org
	 * @param string $name the name of the URL to return
	 * @param bool|string $trail whether to include a trailing slash, or a string to use as the trailing value.  Default: false
	 * @return string URL
	 */
	public static function get_url( $name, $trail = false )
	{
		$url = '';

		switch ( strtolower( $name ) ) {
			case 'host':
				$protocol = 'http';
				// If we're running on a port other than 80, i
				// add the port number to the value returned
				// from host_url
				$port = 80; // Default in case not set.
				$port = Config::get( 'custom_http_port', isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $port );
				$portpart = '';
				$host = Site::get_url( 'hostname' );
				// if the port isn't a standard port, and isn't part of $host already, add it
				if ( ( $port != 80 ) && ( $port != 443 ) && ( MultiByte::substr( $host, MultiByte::strlen( $host ) - strlen( $port ) ) != $port ) ) {
					$portpart = ':' . $port;
				}
				if ( isset( $_SERVER['HTTPS'] ) && !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) {
					$protocol = 'https';
				}
				$protocol = Config::get( 'custom_http_protocol', $protocol );
				$url = $protocol . '://' . $host . $portpart;
				break;
			case 'filmio':
				if(self::$filmio_url == null) {
					self::$filmio_url = Site::get_url( 'host' );
					// Am I installed into a subdir?
					$path = trim( dirname( Site::script_name() ), '/\\' );
					if ( '' != $path ) {
						self::$filmio_url .= '/' . $path;
					}
				}
				$url = self::$filmio_url;
				break;
			case 'site':
				$url = Site::get_url( 'filmio' );
				// Am I in a Filmio subdir site?
				if( self::$config_type == Site::CONFIG_SUBDIR ) {
					$url = Utils::end_in_slash($url) . self::$config_urldir;
				}
				break;
			case 'user':
				$url = Site::get_url( 'filmio', true ) . Site::get_path( 'user' );
				break;
			case 'theme':
				$theme = Themes::get_theme_dir();
				if ( file_exists( Site::get_dir( 'config' ) . '/themes/' . $theme ) ) {
					$url = Site::get_url( 'user' ) .  '/themes/' . $theme;
				}
				elseif ( file_exists( FILMIO_PATH . '/user/themes/' . $theme ) ) {
					$url = Site::get_url( 'filmio' ) . '/user/themes/' . $theme;
				}
				elseif ( file_exists( FILMIO_PATH . '/3rdparty/themes/' . $theme ) ) {
					$url = Site::get_url( 'filmio' ) . '/3rdparty/themes/' . $theme;
				}
				else {
					$url = Site::get_url( 'filmio' ) . '/system/themes/' . $theme;
				}
				break;
			case 'admin':
				$url = Site::get_url( 'site' ) . '/admin';
				break;
			case 'admin_theme':
				$url = Site::get_url( 'filmio' ) . '/system/admin';
				break;
			case 'login':
				$url = Site::get_url( 'site' ) . '/auth/login';
				break;
			case 'logout':
				$url = Site::get_url( 'site' ) . '/auth/logout';
				break;
			case 'system':
				$url = Site::get_url( 'filmio' ) . '/system';
				break;
			case 'vendor':
			case 'scripts':
				$url = Site::get_url( 'system' ) . '/vendor';
				break;
			case '3rdparty':
				// this should be removed at a later date as it will cause problems
				// once 'vendor' is adopted, dump the condition!
				if ( file_exists( FILMIO_PATH . '/3rdparty' ) ) {
					$url = Site::get_url( 'filmio' ) . '/3rdparty';
				}
				else {
					$url = Site::get_url( 'vendor' );
				}
				break;
			case 'hostname':
				// HTTP_HOST is not set for HTTP/1.0 requests
				$url = ( $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0' || !isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
				break;
		}
		$url .= Utils::trail( $trail );
		$url = Plugins::filter( 'site_url_' . $name, $url );
		return $url;
	}

	/**
	 * get_path returns a relative URL path, without leading protocol or host
	 *	'base' returns the URL sub-directory in which Filmio is installed, if any.
	 *  'filmio' returns the path of Filmio
	 *	'user' returns one of the following:
	 *		user
	 *		user/sites/x.y.z
	 *	'theme' returns one of the following:
	 *		/user/themes/theme_name
	 *		/user/sites/x.y.z/themes/theme_dir
	 * @param string $name the name of the path to return
	 * @param bool|string $trail whether to include a trailing slash, or a string to use as the trailing value.  Default: false
	 * @return mixed
	 */
	public static function get_path( $name, $trail = false )
	{
		$path = '';
		switch ( strtolower( $name ) ) {
			case 'base':
			case 'filmio':
				$path = rtrim( dirname( Site::script_name() ), '/\\' );
				if(self::$config_urldir != '') {
					$path .= '/' . self::$config_urldir;
				}
				break;
			case 'user':
				if ( Site::is( 'main' ) ) {
					$path = 'user';
				}
				else {
					$path = ltrim( str_replace( FILMIO_PATH, '', Site::get_dir( 'config' ) ), '/' );
				}
				break;
			case 'theme':
				$theme = Themes::get_theme_dir();
				if ( file_exists( Site::get_dir( 'config' ) . '/themes/' . $theme ) ) {
					$path = Site::get_path( 'user' ) . '/themes/' . $theme;
				}
				elseif ( file_exists( FILMIO_PATH . '/3rdparty/themes/' . $theme ) ) {
					$path = Site::get_path( 'filmio' ) . '/3rdparty/themes/' . $theme;
				}
				else {
					$path = Site::get_path( 'base' ) . '/user/themes/' . $theme;
				}
				break;
		}
		$path .= Utils::trail( $trail );
		// if running Filmio in docroot, get_url('base') will return
		// a double slash.  Let's fix that.
		$path = str_replace( '//', '/', $path );
		$path = Plugins::filter( 'site_path_' . $name, $path );
		return $path;
	}

	/**
	 * get_dir returns a complete filesystem path to the requested item
	 *	'config_file' returns the complete path to the config.php file, including the filename
	 *	'config' returns the path of the directory containing config.php
	 *	'user' returns the path of the user directory
	 *	'theme' returns the path of the site's active theme
	 *  'admin_theme' returns the path to the admin directory
	 *  'vendor' returns the path to the vendor directory
	 * @param string $name the name of the path item to return
	 * @param bool|string $trail whether to include a trailing slash, or a string to use as the trailing value.  Default: false
	 * @return string Path
	 */
	public static function get_dir( $name, $trail = false )
	{
		$path = '';

		switch ( strtolower( $name ) ) {
			case 'config_file':
				$path = Site::get_dir( 'config' ) . '/config.php';
				break;
			case 'config':
				if ( self::$config_path ) {
					return self::$config_path;
				}

				self::$config_path = FILMIO_PATH;

				$config_dirs = preg_replace( '/^' . preg_quote( FILMIO_PATH, '/' ) . '\/user\/sites\/(.*)/', '$1', Utils::glob( FILMIO_PATH . '/user/sites/*', GLOB_ONLYDIR ) );

				if ( empty( $config_dirs ) ) {
					return self::$config_path;
				}

				// Collect host parts
				$server = InputFilter::parse_url( Site::get_url( 'filmio' ) );
				$request = array();
				if ( isset( $server['port'] ) && $server['port'] != '' && $server['port'] != '80' ) {
					$request[] = $server['port'];
				}
				$request = array_merge($request, explode('.', $server['host']));
				// Collect the subdirectory(s) the core is located in to determine the base path later
				// Don't add them to $request, they will be added with $_SERVER['REQUEST_URI']
				$basesegments = count($request);
				if(!empty($server['path'])) {
					$coresubdir = explode( '/', trim( $server['path'], '/' ) );
					$basesegments += count($coresubdir);
				}

				// Preserve $request without directories for fallback
				$request_base = $request;
				// Add directory part
				if(!empty(trim( $_SERVER['REQUEST_URI'], '/' ))) {
					$subdirectories = explode( '/', trim( $_SERVER['REQUEST_URI'], '/' ) );
					$request = array_merge($request, $subdirectories);
				}
				// Now cut parts until we found a matching site directory
				$cuts = 0;
				$dir_cuts = 0;
				do {
					$match = implode('.', $request);
					if ( in_array( $match, $config_dirs ) ) {
						self::$config_dir = $match;
						self::$config_path = FILMIO_PATH . '/user/sites/' . self::$config_dir;
						self::$config_type = ( isset($subdirectories) && array_intersect($subdirectories, $request) == $subdirectories ) ? Site::CONFIG_SUBDIR : Site::CONFIG_SUBDOMAIN;
						self::$config_urldir = implode('/', array_slice($request, $basesegments + $cuts));
						break;
					}

					// Cut a part from the beginning
					array_shift($request);
					$cuts--;
					
					// Do not fallback to only the directory part, instead, cut one and start over
					if($basesegments + $cuts <= 0 && isset($subdirectories) && $dir_cuts < count($subdirectories)) {
						$cuts = 0;
						$dir_cuts++;
						$request = array_merge($request_base, array_slice($subdirectories, 0, count($subdirectories) - $dir_cuts));
					}
					// What does the following check do?!
					if ( $cuts < -10 ) {
						echo $cuts;
						var_dump($request);
						die('too many ');
					}
				} while ( count($request) > 0 );
				$path = self::$config_path;
				break;
			case 'user':
				if ( Site::get_dir( 'config' ) == FILMIO_PATH ) {
					$path = FILMIO_PATH . '/user';
				}
				else {
					$path = Site::get_dir( 'config' );
				}
				break;
			case 'theme':
				$theme = Themes::get_theme_dir();
				if ( file_exists( Site::get_dir( 'config' ) . '/themes/' . $theme ) ) {
					$path = Site::get_dir( 'user' ) . '/themes/' . $theme;
				}
				elseif ( file_exists( FILMIO_PATH . '/user/themes/' . $theme ) ) {
					$path = FILMIO_PATH . '/user/themes/' . $theme;
				}
				elseif ( file_exists( FILMIO_PATH . '/3rdparty/themes/' . $theme ) ) {
					$path = Site::get_dir( 'filmio' ) . '/3rdparty/themes/' . $theme;
				}
				else {
					$path = FILMIO_PATH . '/system/themes/' . $theme;
				}
				break;
			case 'admin_theme':
				$path = FILMIO_PATH . '/system/admin';
				break;
			case 'vendor':
				$path = FILMIO_PATH . '/system/vendor';
				break;
		}
		$path .= Utils::trail( $trail );
		$path = Plugins::filter( 'site_dir_' . $name, $path );
		return $path;
	}

	/**
	 * out_url echos out a URL
	 * @param string $url the URL to display
	 * @param bool|string $trail whether or not to include a trailing slash, or a string to use as the trailing value.  Default: false
	 */
	public static function out_url( $url, $trail = false )
	{
		echo Site::get_url( $url, $trail );
	}

	/**
	 * out_path echos a URL path
	 * @param string $path the URL path to display
	 * @param bool $trail whether or not to include a trailing slash, or a string to use as the trailing value.  Default: false
	 */
	public static function out_path( $path, $trail = false )
	{
		echo Site::get_path( $path, $trail );
	}

	/**
	 * our_dir echos our a filesystem directory
	 * @param string $dir the filesystem directory to display
	 * @param bool $trail whether or not to include a trailing slash, or a string to use as the trailing value.  Default: false
	 */
	public static function out_dir( $dir, $trail = false )
	{
		echo Site::get_dir( $dir, $trail );
	}
}

?>
