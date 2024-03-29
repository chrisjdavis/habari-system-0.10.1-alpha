<?php
/**
 * @package Filmio
 *
 */

namespace Filmio;

/**
* Filmio Tags Class
*
*/
class Tags extends Vocabulary
{
	protected static $vocabulary = 'tags';
	protected static $object_type = 'post';

	/**
	 * Return a tag based on an id, tag text or slug
	 *
	 * @param string|integer $tag A tag's text, slug, or id
	 * @return Tag The tag object
	 */
	public static function get_one( $tag )
	{
		$term = self::vocabulary()->get_term( $tag );
		if ( !$term instanceOf Term ) {
			return false;
		}
		return $term;
	}

	/**
	 * Return a tag based on a tag's text
	 *
	 * @param string $tag The tag's text (not slug)
	 * @return Tag A Tag object
	 */
	public static function get_by_text( $tag )
	{
		return self::get_one( $tag );
	}

	/**
	 * Return a tag based on a tag's text
	 *
	 * @param string $tag The tag slug
	 * @return Tag A Tag object
	 */
	public static function get_by_slug( $tag )
	{
		return self::get_one( $tag );
	}

	/**
	 * Returns a Tag object based on a supplied ID
	 *
	 * @param Integer $tag The ID of the tag to retrieve
	 * @return Tag A Tag object
	 */
	public static function get_by_id( $tag )
	{
		return self::get_one( (int) $tag );
	}

	/**
	 * Returns the tags vocabulary
	 *
	 * @return Vocabulary The tags vocabulary
	 */
	public static function vocabulary()
	{
		return Vocabulary::get( self::$vocabulary );
	}

	/**
	 * Returns a list of terms from this vocabulary ordered by frequency of use on the post type specified
	 *
	 * @param int $limit If supplied, limits the results to the specified number
	 * @param mixed $post_type If a name or id of a post type is supplied, limits the results to the terms applying to that type
	 * @param int $min If supplied, limits the results to tags associated with at least this number of posts
	 * @param int $max If supplied, limits the results to tags associated with at most this number of posts
	 * @param mixed $search If supplied, limits the results to tags containing that value in their display string
	 * @return Tags A Tags instance containing the terms, each having an additional property of "count" that tells how many times the term was used
	 */
	public static function get_by_frequency($limit = null, $post_type = null, $min = 1, $max = null, $search = null, $orderby = 'count', $inverse = false)
	{
		$query = '
			SELECT t.*, tp.object_id, count(tp.object_id) as `count`
			FROM {terms} t
			LEFT JOIN {object_terms} tp ON t.id=tp.term_id
			LEFT JOIN {posts} p ON tp.object_id=p.id
			LEFT JOIN {object_types} ot ON tp.object_type_id=ot.id and ot.name="post"
			WHERE t.vocabulary_id = ?';

		$params = array(Tags::vocabulary()->id);

		if ( isset($post_type) ) {
			$query .= ' AND p.content_type = ?';
			$params[] = Post::type($post_type);
		}

		if ( isset($search) ) {
			$query .= " AND term_display LIKE ?";
			$params[] = '%' . $search . '%';
		}

		$query .= ' GROUP BY t.id';

		if( is_int($min) || is_int($max) ) {
			$query .= ' HAVING';
		}
		if( is_int($min) ) {
			$query .= " count >= {$min}";
		}
		if( is_int($min) && is_int($max) ) {
			$query .= ' AND';
		}
		if( is_int($max) ) {
			$query .= " count <= {$max}";
		}

		$valid_orderbys = array('pubdate', 'count', 'term_display');
		if(!in_array($orderby, $valid_orderbys)) {
			$orderby = 'count';
		}

		$query .= " ORDER BY `{$orderby}`";
		// Inverse both directions so the result is actually reversed tag by tag and not in blocks
		if($inverse) {
			$query .= ' ASC, term_display DESC';
		}
		else {
			$query .= ' DESC, term_display ASC';
		}


		if ( is_int($limit) ) {
			$query .= ' LIMIT ' . $limit;
		}

		$tags = DB::get_results(
			$query,
			$params,
			'Term'
		);

		return $tags ? new Terms($tags) : false;
	}

	/**
	 * Returns the default type Tags uses
	 *
	 * @return String The default type name
	 */
	public static function object_type()
	{
		return self::$object_type;
	}

	/**
	 * Save the tags associated to this object into the terms and object_terms tables
	 *
	 * @param Array $tags strings. The tag names to associate to the object
	 * @param Integer $object_id. The id of the object being tagged
	 * @param String $object_type. The name of the type of the object being tagged. Defaults to post
	 *
	 * @return boolean. Whether the associating succeeded or not. true
	 */
	public static function save_associations( $terms, $object_id, $object_type = 'post' )
	{
		if ( ! $terms instanceof Terms ) {
			$terms = Terms::parse( $terms, 'Tag', Tags::vocabulary() );
		}
		return self::vocabulary()->set_object_terms( $object_type, $object_id, $terms );
	}

	/**
	 * Parse tag parameters from a URL string
	 *
	 * @param String $tags The URL parameter string
	 *
	 * @return Array. Associative array of included and excluded tags
	 */
	public static function parse_url_tags( $tags, $objectify = false )
	{
		$tags = explode( ' ', $tags );
		$exclude_tag = array();
		$include_tag = array();
		foreach ( $tags as $tag ) {
			if ( MultiByte::substr( $tag, 0, 1 ) == '-' ) {
				$tag = MultiByte::substr( $tag, 1 );
				$exclude_tag[] = $objectify ? Tags::get_one(Utils::slugify( $tag )) : Utils::slugify( $tag );
			}
			else {
				$include_tag[] = $objectify ? Tags::get_one(Utils::slugify( $tag )) : Utils::slugify( $tag );
			}
		}

		return compact('include_tag', 'exclude_tag');
	}

	/**
	 * Return a terms that match a substring criteria
	 * @param string $q Criteria of a term to match
	 * @return Terms The matching terms
	 */
	public static function search($q)
	{
		$terms = self::vocabulary()->search_term( '%' . $q . '%' );
		if ( !$terms instanceOf Terms ) {
			return new Terms();
		}
		return $terms;
	}
}
?>
