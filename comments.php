<?php
/**
 * Default WordPress comments template.
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				esc_html( _n( '%s Comment', '%s Comments', get_comments_number(), 'blueprint' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>

		<ol class="comment-list px-0">
			<?php
			wp_list_comments(
				[
					'style'      => 'ol',
					'short_ping' => true,
					'avatar_size'=> 48,
					'callback'   => 'blueprint_comment',
				]
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() ) :
		?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'blueprint' ); ?></p>
	<?php endif; ?>

	<?php
	$commenter     = wp_get_current_commenter();
	$require_name  = (bool) get_option( 'require_name_email' );
	$aria_required = $require_name ? " aria-required='true'" : '';

	$fields = [
		'author' => sprintf(
			'<div class="mb-3 comment-form-author"><label class="form-label" for="author">%s%s</label><input class="form-control" id="author" name="author" type="text" value="%s" size="30"%s /></div>',
			esc_html__( 'Name', 'blueprint' ),
			$require_name ? ' <span class="required">*</span>' : '',
			esc_attr( $commenter['comment_author'] ?? '' ),
			$aria_required
		),
		'email'  => sprintf(
			'<div class="mb-3 comment-form-email"><label class="form-label" for="email">%s%s</label><input class="form-control" id="email" name="email" type="email" value="%s" size="30"%s /></div>',
			esc_html__( 'Email', 'blueprint' ),
			$require_name ? ' <span class="required">*</span>' : '',
			esc_attr( $commenter['comment_author_email'] ?? '' ),
			$aria_required
		),
		'url'    => sprintf(
			'<div class="mb-3 comment-form-url"><label class="form-label" for="url">%s</label><input class="form-control" id="url" name="url" type="url" value="%s" size="30" /></div>',
			esc_html__( 'Website', 'blueprint' ),
			esc_attr( $commenter['comment_author_url'] ?? '' )
		),
	];

	$comment_field = sprintf(
		'<div class="mb-3 comment-form-comment"><label class="form-label" for="comment">%s <span class="required">*</span></label><textarea class="form-control" id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required"></textarea></div>',
		esc_html__( 'Comment', 'blueprint' )
	);

	comment_form(
		[
			'class_form'         => 'comment-form',
			'class_submit'       => 'btn btn-primary',
			'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title h5 mb-3">',
			'title_reply_after'  => '</h3>',
			'comment_field'      => $comment_field,
			'fields'             => $fields,
			'submit_field'       => '<div class="form-submit mt-3">%1$s %2$s</div>',
		]
	);
	?>
</div>