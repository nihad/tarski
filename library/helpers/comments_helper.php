<?php
/**
 * @package WordPress
 * @subpackage Tarski
 */

/**
 * Ties the date and time together.
 * 
 * This function makes the comment date and time output more translateable.
 * 
 * @since 2.0
 * 
 * @return string
 * 
 * @hook filter tarski_comment_datetime
 * Filters the date and time printed with a comment.
 */
function tarski_comment_datetime() {
	$datetime = sprintf(
		__('%1$s at %2$s','tarski'),
		get_comment_date(),
		get_comment_time()
	);
	return apply_filters('tarski_comment_datetime', $datetime);
}

/**
 * Strips the http:// prefix from OpenID names.
 * 
 * @since 2.0
 * 
 * @global object $comment_author
 * @return string $comment_author
 */
function tidy_openid_names($comment_author) {
	global $comment;
	$comment_author =  str_replace('http://', '', $comment_author);
	$comment_author = rtrim($comment_author, '/');
	return $comment_author;
}

/**
 * Remove some of the cruft generated by the get_avatar function.
 * 
 * Adds proper alternate text for the image, replaces single quotes with double
 * ones for markup consistency, and removes the height and width attributes so
 * a naturally sized default image can be employed (e.g. a 1x1 pixel
 * transparent GIF so there appears to be no default image).
 * 
 * @since 2.1
 * 
 * @param string $avatar
 * @param string $id_or_email
 * @param string $size
 * @param string $default
 * @return mixed
 */
function tidy_avatars($avatar, $id_or_email, $size, $default) {
	$url = get_comment_author_url();
	$author_alt = sprintf( __('%s&#8217;s avatar'), get_comment_author() );
	$avatar = preg_replace("/height='[\d]+' width='[\d]+'/", '', $avatar);
	
	if ( !is_admin() )
		$avatar = preg_replace("/'/", '"', $avatar);
	
	$avatar = preg_replace('/alt=""/', "alt=\"$author_alt\"", $avatar);
	
	return $avatar;
}

/**
 * Linked avatar images for Tarski.
 * 
 * Links to the comment author's home page if they have one, and just returns
 * the image otherwise.
 * 
 * @since 2.3
 * 
 * @return string
 */
function tarski_avatar() {
	$default = '';
	
	if ('' == get_option('avatar_default'))
		$default = get_bloginfo('template_directory') . '/images/avatar.png';
	
	$avatar = get_avatar(get_comment_author_email(), '50', $default);
	
	if (!$avatar)
		return false;
	
	$url = get_comment_author_url();
	
	if (empty($url) || preg_match('/^\s*http:\/\/\s*$/', $url)) {
		return $avatar;
	} else {
		return "<a class=\"avatar-link\" href=\"$url\" rel=\"external nofollow\">$avatar</a>";
	}
}

/**
 * Make the Tarski avatar selectable.
 * 
 * Adds the Tarski avatar to the Discussion options page, allowing it to be
 * selected but also allowing users to choose other avatars.
 * 
 * @since 2.3
 * 
 * @param array $avatar_defaults
 * @return string
 */
function tarski_default_avatar($avatar_defaults) {
	$tarski_avatar = get_bloginfo('template_directory') . '/images/avatar.png';
	$avatar_defaults[$tarski_avatar] = 'Tarski';
	return $avatar_defaults;
}

/**
 * Returns a comment author's name, wrapped in a link if present.
 * 
 * It also includes hCard microformat markup.
 * @link http://microformats.org/wiki/hcard
 * 
 * @since 2.0
 * 
 * @global object $comment
 * @return string
 * 
 * @hook filter get_comment_author_link
 * Native WordPress filter on comment author links.
 * @hook filter tarski_comment_author_link
 * Tarski-specific filter on comment author links.
 */
function tarski_comment_author_link() {
	global $comment;
	$url = get_comment_author_url();
	$author = get_comment_author();

	if(empty($url) || 'http://' == $url) {
		$return = sprintf(
			'<span class="fn">%s</span>',
			$author
		);
	} else {
		$return = sprintf(
			'<a class="url fn" href="%1$s" rel="external nofollow">%2$s</a>',
			$url,
			$author
		);
	}

	$return =  apply_filters('get_comment_author_link', $return);
	$return = apply_filters('tarski_comment_author_link', $return);
	return $return;
}

/**
 * Outputs a text field and associated label.
 * 
 * Used in the comments reply form to reduce duplication and clean up the
 * template. Adds a wrapper div around the label and input field for ease of
 * styling.
 * 
 * @since 2.4
 * @uses required_field()
 * 
 * @param string $field
 * @param string $label
 * @param string $value
 * @param boolean $required
 * @param integer $size
 */
function comment_text_field($field, $label, $value = '', $required = false, $size = 20) { ?>
	<div class="text-wrap">
		<label for="<?php echo $field; ?>"><?php printf($label, required_field($required)); ?></label>
		<input class="<?php echo comment_field_classes(); ?>" type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo $value; ?>" size="<?php echo $size; ?>" />
	</div>
<?php }

/**
 * Builds the HTML classes for comment form text fields.
 * 
 * @since 2.4
 * 
 * @param string $classes
 * @param boolean $required
 * @return string
 */
function comment_field_classes($classes = '', $required = false) {
	$classes = trim($classes);
	if (strlen($classes) < 1) $classes = 'text';
	if ($required) $classes .= ' required';
	return apply_filters('comment_field_classes', $classes, $required);
}

/**
 * Returns a notice stating that a field is required.
 * 
 * Thrown into a function for reusability's sake, and to reduce the number of
 * sprintf()s and localisation strings cluttering up the comment form.
 * 
 * @since 2.4
 * 
 * @param boolean $required
 * @return string
 */
function required_field($required = true) {
	if ($required) return sprintf(
		'<span class="req-notice">(%s)</span>',
		__('required', 'tarski')
	);
}

?>