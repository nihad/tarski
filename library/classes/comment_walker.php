<?php

class TarskiCommentWalker extends Walker_Comment {
	/**
	 * @see Walker::start_lvl()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	function start_lvl(&$output, $depth, $args) {
		$GLOBALS['comment_depth'] = $depth + 1;

		switch ( $args['style'] ) {
			case 'div':
				break;
			case 'ol':
				echo "<ol class='children'>\n";
				break;
			default:
			case 'ul':
				echo "<ul class='children'>\n";
				break;
		}
		
		$this->first_on_level = true;
	}
	
	/**
	 * @see Walker::start_el()
	 * @since unknown
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment Comment data object.
	 * @param int $depth Depth of comment in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $comment, $depth, $args) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		
		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $comment, $args, $depth);
			return;
		}
		
		if ($this->first_on_level) {
			$first = "comment-lvl-first";
			$this->first_on_level = false;
		}
		
		$reply_link_opts = array_merge($args, array(
			'add_below' => 'comment-wrapper',
			'depth' => $depth,
			'max_depth' => $args['max_depth']
		));
		
		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);
?>
		<li <?php comment_class($first); ?> id="comment-<?php comment_ID(); ?>">
			<div class="comment-wrapper" id="comment-wrapper-<?php comment_ID(); ?>">
	          	<?php if ($comment->comment_approved == '0') { ?>
	          		<p class="moderated"><strong><?php _e('Your comment is awaiting moderation.', 'tarski'); ?></strong></p>
	          	<?php } ?>
	
				<?php echo tarski_avatar(); ?>
				
				<p class="comment-meta commentmetadata"><?php printf(__('%1$s on %2$s', 'tarski'),
					'<span class="comment-author vcard">' . tarski_comment_author_link() . '</span>',
					'<span class="comment-permalink"><a title="' .  __('Permalink to this comment','tarski') . '" href="' . htmlspecialchars(get_comment_link($comment->comment_ID, $page)) . '">' . tarski_comment_datetime() .'</a></span>'
				); edit_comment_link(__('edit','tarski'), ' <span class="comment-edit">(', ')</span>'); ?>
				</p>
				
				
				<div class="comment-content content">
					<?php comment_text(); ?>
				</div>
				
				<p class="reply"><?php comment_reply_link($reply_link_opts); ?></p>
	        </div>
<?
	}
}

?>