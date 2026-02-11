<?php

use lray138\G2\Lst;

require "vendor/autoload.php";

require_once get_template_directory() . '/inc/admin/editor.php';
require_once get_template_directory() . '/inc/admin/allow-year-pages.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/partials.php';
require_once get_template_directory() . '/inc/admin/clone-carbon-page.php';

/**
 * Allow SVG uploads for administrators only.
 */
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('manage_options')) {
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
    }
    return $mimes;
});

/**
 * Help WP recognize SVGs in the media library.
 */
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (preg_match('/\.svgz?$/i', $filename)) {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}, 10, 4);

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'cf-assoc-enhancer',
        get_template_directory_uri() . '/js/tmp.js',
        [],
        null,
        true
    );
});

require_once get_template_directory() . '/app/php/admin/index.php';

/**
 * Add "Parent" and "Children" row actions to Pages list table.
 */
add_filter('page_row_actions', function (array $actions, WP_Post $post) {
    if ($post->post_type !== 'page') return $actions;

    $new = [];

    foreach ($actions as $key => $html) {
        $new[$key] = $html;

        // Insert links right after "Edit"
        if ($key === 'edit') {

            /** -------------------------
             * Parent link (if exists)
             * ------------------------- */
            if ($post->post_parent) {
                $parent_url = add_query_arg(
                    [
                        'post_type'   => 'page',
                        'post_parent' => $post->post_parent,
                    ],
                    admin_url('edit.php')
                );

                $new['parent'] = sprintf(
                    '<a href="%s">Parent</a>',
                    esc_url($parent_url)
                );
            }

            /** -------------------------
             * Children link (if exists)
             * ------------------------- */
            $has_children = (bool) get_pages([
                'post_type'   => 'page',
                'post_parent' => $post->ID,
                'number'      => 1,
                'fields'      => 'ids',
            ]);

            if ($has_children) {
                $children_url = add_query_arg(
                    [
                        'post_type'   => 'page',
                        'post_parent' => $post->ID,
                    ],
                    admin_url('edit.php')
                );

                $new['children'] = sprintf(
                    '<a href="%s">Children</a>',
                    esc_url($children_url)
                );
            }
        }
    }

    return $new;
}, 10, 2);



/////

function register_contact_submissions_cpt() {
    $args = [
        'label'             => 'Contact Submissions',
        'public'            => false, // Not visible to users
        'show_ui'           => true, // Visible in admin panel
        'supports'          => ['title', 'editor', 'custom-fields'],
        'capability_type'   => 'post',
        'menu_position'     => 20,
        'menu_icon'         => 'dashicons-email',
    ];
    register_post_type('contact_submission', $args);
}
add_action('init', 'register_contact_submissions_cpt');



/// 

add_action('init', function () {
    register_taxonomy_for_object_type('post_tag', 'attachment');
});


///

function getHomeImages(): Lst {
    $out =  Lst::of(get_posts([
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => 'show-home',
            ],
        ],
    ]));    

    return $out;
}

function renderMasonry(Lst $l) {

    return $l->map(function($x) {
        if(is_image($x)) {
            $src = wp_get_attachment_image_src($x->ID, 'full')[0];
            return "<div class=\"col-12 col-md-4 mb-4\"><img class=\"img-fluid rounded\" src=\"{$src}\" alt=\"\"></div>";
        }
        return "<div class=\"col-12 col-md-4\">f</div>";
    })
    ->join("");
}

add_action('admin_enqueue_scripts', function ($hook) {

    // Only load on Edit Post / Add New screens
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    // --- Carbon Fields / TinyMCE enhancements ---
    // (safe even if Carbon Fields loads later)
    if (class_exists('Carbon_Fields\Carbon_Fields')) {
        wp_enqueue_script(
            'cf-tinymce-sticky',
            get_stylesheet_directory_uri() . '/assets/cf-tinymce-sticky.js',
            [],
            '1.0.0',
            true
        );
    }

    // --- Sticky Publish + Scroll Restore ---
    wp_enqueue_script(
        'sticky-major-publishing-actions',
        get_stylesheet_directory_uri() . '/assets/sticky-publish-actions.js',
        [],
        '1.0.3',
        true
    );
});

add_action('wp_enqueue_scripts', function () {
    if (!is_user_logged_in()) return;
    if (!is_singular()) return; // posts, pages, CPTs only
  
    wp_enqueue_script(
      'receive-update-broadcast',
      get_template_directory_uri() . '/assets/receive-update-broadcast.js',
      [],
      null,
      true
    );
});






// Dashboard

add_action('wp_dashboard_setup', function () {

    global $wp_meta_boxes;

    wp_add_dashboard_widget(
        'blueprint_home_widget',
        'Blueprint CMF',
        'blueprint_render_home_widget'
    );

    // Move widget to top
    $widget = $wp_meta_boxes['dashboard']['normal']['core']['blueprint_home_widget'];
    unset($wp_meta_boxes['dashboard']['normal']['core']['blueprint_home_widget']);
    $wp_meta_boxes['dashboard']['normal']['high']['blueprint_home_widget'] = $widget;

});

function blueprint_render_home_widget() {
    ?>
    <p><strong>Blueprint CMF</strong></p>

    <p>
        Composable. Predictable.<br>
        Built for the long haul.
    </p>

    <ul>
        <li><a href="<?= admin_url('edit.php?post_type=page'); ?>">Pages</a></li>
        <li><a href="<?= admin_url('edit.php?post_type=post'); ?>">Posts</a></li>
        <li><a href="<?= admin_url('themes.php'); ?>">Theme Settings</a></li>
    </ul>
    <?php
}

add_theme_support('post-thumbnails');



////// comments //////

function blueprint_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$comment_id = get_comment_ID();
	$permalink  = get_comment_link( $comment_id );
	$avatar     = get_avatar( $comment, 48, '', '', [ 'class' => 'rounded-circle' ] );
	$author     = get_comment_author_link( $comment_id );
	$date       = get_comment_date( '', $comment_id );
	$edit_link  = get_edit_comment_link( $comment_id );
	$indent     = $depth > 1 ? ' ms-4' : '';
	$reply_link = get_comment_reply_link(
		array_merge(
			$args,
			[
				'add_below'  => 'comment',
				'depth'      => $depth,
				'max_depth'  => $args['max_depth'],
				'reply_text' => __( 'Reply', 'blueprint' ),
			]
		),
		$comment_id
	);
	?>
	<li <?php comment_class( 'card mb-3' . $indent, $comment_id ); ?> id="comment-<?php echo esc_attr( $comment_id ); ?>">
		<div class="card-body">
			<div class="d-flex">
				<?php echo $avatar; ?>
				<div class="ms-3 flex-grow-1">
					<h6 class="mb-1">
						<?php echo $author; ?>
						<small class="text-muted fw-normal">
							&middot;
							<a href="<?php echo esc_url( $permalink ); ?>" class="text-muted text-decoration-none">
								<?php echo esc_html( $date ); ?>
							</a>
						</small>
					</h6>
					<div class="mb-2 comment-content">
						<?php comment_text(); ?>
					</div>
					<div class="d-flex align-items-center gap-3 small">
						<?php if ( $reply_link ) : ?>
							<span class="link-secondary text-decoration-none"><?php echo $reply_link; ?></span>
						<?php endif; ?>
						<?php if ( $edit_link ) : ?>
							<a href="<?php echo esc_url( $edit_link ); ?>" class="link-secondary text-decoration-none">
								<?php esc_html_e( 'Edit', 'blueprint' ); ?>
							</a>
						<?php endif; ?>
						<!-- <a href="<?php echo esc_url( $permalink ); ?>" class="link-secondary text-decoration-none">
							<?php esc_html_e( 'Link', 'blueprint' ); ?>
						</a> -->
					</div>
				</div>
			</div>
		</div>
	</li>
	<?php
}

add_filter('comments_open', function ($open, $post_id) {
    $template = get_page_template_slug($post_id);

    if ($template === 'template/blog-page.php') {
        return true;
    }

    return $open;
}, 10, 2);


add_action('save_post_page', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_page', $post_id)) return;

    $template = get_page_template_slug($post_id);

    if ($template === 'template/blog-page.php') {
        if (!comments_open($post_id)) {
            wp_update_post([
                'ID' => $post_id,
                'comment_status' => 'open',
            ]);
        }
    }
});



/////

function blueprint_get_user_avatar(
    int $user_id,
    int $size = 48,
    array $attrs = []
): string {

    // 1) Try Blueprint / Carbon Fields avatar
    $avatar_id = carbon_get_user_meta($user_id, 'bp_avatar_upload');

    if ($avatar_id) {
        $src = wp_get_attachment_image_url($avatar_id, 'thumbnail');

        if ($src) {
            $class = $attrs['class'] ?? 'rounded-circle';
            $alt   = esc_attr(get_the_author_meta('display_name', $user_id));

            return sprintf(
                '<img src="%s" width="%d" height="%d" class="%s" alt="%s">',
                esc_url($src),
                $size,
                $size,
                esc_attr($class),
                $alt
            );
        }
    }

    // 2) Fallback: WordPress avatar (Gravatar)
    return get_avatar($user_id, $size, '', '', $attrs);
}

add_filter('get_avatar', function ($avatar, $id_or_email, $size) {
    if (is_object($id_or_email)) {
        if ($id_or_email instanceof WP_User) {
            $user = $id_or_email;
        } elseif ($id_or_email instanceof WP_Comment) {
            if (!empty($id_or_email->user_id)) {
                $user = get_user_by('id', (int) $id_or_email->user_id);
            } elseif (!empty($id_or_email->comment_author_email)) {
                $user = get_user_by('email', $id_or_email->comment_author_email);
            } else {
                $user = null;
            }
        } else {
            $user = null;
        }
    } else {
        $user = is_numeric($id_or_email)
            ? get_user_by('id', $id_or_email)
            : get_user_by('email', $id_or_email);
    }

    if (!$user) return $avatar;

    if(function_exists('carbon_get_user_meta')) {
    $avatar_id = carbon_get_user_meta($user->ID, 'bp_avatar_upload');
    if (!$avatar_id) return $avatar;

    $src = wp_get_attachment_image_url($avatar_id, 'thumbnail');
    if (!$src) return $avatar;

    return sprintf(
        '<img src="%s" width="%d" height="%d" class="rounded-circle">',
        esc_url($src),
        $size,
        $size
    );
}

}, 10, 3);
