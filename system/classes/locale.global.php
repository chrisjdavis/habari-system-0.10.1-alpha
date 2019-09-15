<?php

/**
 * This file puts the global locale functions into the global namespace
 */

use Filmio\Locale;

/**
 * Echo a version of the string translated into the current locale, alias for FilmioLocale::_e()
 *
 * @param string $text The text to translate
 * @param array $args
 * @param string $domain
 * @return void
 */
function _e( $text, $args = array(), $domain = 'filmio' )
{
	Locale::_e( $text, $args, $domain );
}

/**
 * Echo singular or plural version of the string, translated into the current locale, based on the count provided,
 * alias for FilmioLocale::_ne()
 * @param string $singular The singular form
 * @param string $plural The plural form
 * @param string $count The count
 * @param string $domain
 */
function _ne( $singular, $plural, $count, $domain = 'filmio' )
{
	Locale::_ne( $singular, $plural, $count, $domain );
}

/**
 * Return a version of the string translated into the current locale, alias for FilmioLocale::_t()
 *
 * @param string $text The text to translate
 * @param array $args
 * @param string $domain
 * @return string The translated string
 */
function _t( $text, $args = array(), $domain = 'filmio' )
{
	return Locale::_t( $text, $args, $domain );
}

/**
 * Return a singular or plural string translated into the current locale based on the count provided
 *
 * @param string $singular The singular form
 * @param string $plural The plural form
 * @param string $count The count
 * @param string $domain
 * @return string The appropriately translated string
 */
function _n( $singular, $plural, $count, $domain = 'filmio' )
{
	return Locale::_n( $singular, $plural, $count, $domain );
}

/**
 * Given a string translated into the current locale, return the untranslated version of the string.
 * Alias for FilmioLocale::_u()
 *
 * @param string $text The translated string
 * @param string $domain (optional) The domain to search for the message
 * @return string The untranslated string
 */
function _u( $text, $domain = 'filmio' )
{
	return Locale::_u( $text, $domain );
}

?>