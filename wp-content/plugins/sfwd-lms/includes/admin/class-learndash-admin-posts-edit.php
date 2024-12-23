<?php
/**
 * LearnDash Post Edit Base.
 *
 * @since 2.6.0
 * @package LearnDash
 */

use LearnDash\Core\Utilities\Cast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Learndash_Admin_Post_Edit' ) ) {

	/**
	 * Class LearnDash Post Edit Base.
	 *
	 * @since 2.6.0
	 */
	abstract class Learndash_Admin_Post_Edit {

		/**
		 * Post ID being edited.
		 *
		 * @var integer $post_id;
		 */
		protected $post_id = 0;

		/**
		 * Post type supported by this class.
		 *
		 * @var string $post_type Post Type.
		 */
		protected $post_type;

		/**
		 * Common array set to contain the metaboxes shown on the post edit screen.
		 *
		 * @var LearnDash_Settings_Metabox[] $_metaboxes;
		 */
		protected $_metaboxes = array(); //phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

		/**
		 * Whether this post type has a dashboard tab. Default is false.
		 *
		 * @since 4.9.0
		 *
		 * @var bool $has_dashboard_tab
		 */
		protected $has_dashboard_tab = false;

		/**
		 * Public constructor for class.
		 *
		 * @since 2.6.0
		 */
		public function __construct() {
			// Hook into the on-load action for our post_type editor.
			add_action( 'load-post.php', array( $this, 'on_load' ) );
			add_action( 'load-post-new.php', array( $this, 'on_load' ) );
			add_action( 'save_post', array( $this, 'save_post' ), 50, 3 );
			add_action( 'edit_post_' . $this->post_type, array( $this, 'edit_post_post_type' ), 50, 2 );
			add_filter( 'wp_insert_post_parent', array( $this, 'filter_post_parent' ), 30, 4 );

			add_filter( 'enter_title_here', array( $this, 'gutenberg_placeholder_enter_title_here' ), 30, 2 );
			add_filter( 'write_your_story', array( $this, 'gutenberg_placeholder_write_your_story' ), 30, 2 );
			add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 30, 1 );
			add_filter( 'redirect_post_location', array( $this, 'redirect_post_location' ), 30, 2 );

			add_action( 'admin_init', [ $this, 'process_dashboard_tab' ] );
		}

		/**
		 * Processes the dashboard tab.
		 *
		 * @since 4.9.0
		 *
		 * @return void
		 */
		public function process_dashboard_tab(): void {
			// If has dashboard tab, registering filters.

			/**
			 * Filters whether the Dashboard tab is the default tab in the post edit screen.
			 *
			 * @since 4.9.0
			 *
			 * @param bool   $dashboard_tab_is_default Whether the Dashboard tab is the default tab in the post edit screen.
			 * @param string $post_type                The post type.
			 *
			 * @return bool
			 */
			$dashboard_tab_is_default = apply_filters( 'learndash_dashboard_tab_is_default', true, $this->post_type );

			if (
					$this->has_dashboard_tab
					&& $dashboard_tab_is_default
					) {
				add_filter(
					'get_edit_post_link',
					[ $this, 'add_dashboard_tab_in_edit_post_link' ],
					99,
					3
				);

				add_filter(
					'post_row_actions',
					[ $this, 'revert_edit_post_link_in_row_actions' ],
					99,
					2
				);
			}
		}

		/**
		 * Filters the edit post link function to add the Dashboard tab when clicking on the Post title in the Post list.
		 *
		 * @since 4.9.0
		 *
		 * @param string $link    The edit post link.
		 * @param int    $post_id Post ID.
		 * @param string $context The context.
		 *                        We removed the PHP type because MemberPress called get_edit_post_link()
		 *                        function with a $context parameter = NULL, and our customers needed a quick fix.
		 *
		 * @return string
		 */
		public function add_dashboard_tab_in_edit_post_link( $link, $post_id, $context ): string {
			$current_screen = get_current_screen();

			$link = Cast::to_string( $link );

			if (
				$this->has_dashboard_tab
				&& $current_screen
				&& $current_screen->id === 'edit-' . $this->post_type
				&& $context === 'display'
			) {
				return add_query_arg( 'currentTab', 'learndash_' . $this->post_type . '_dashboard', $link );
			}

			return $link;
		}

		/**
		 * Reverts the edit post link in the row actions to the default one.
		 *
		 * @since 4.9.0
		 *
		 * @param array<string,mixed> $actions The row actions.
		 * @param WP_Post             $post    The post object.
		 *
		 * @return array<string,mixed>
		 */
		public function revert_edit_post_link_in_row_actions( array $actions, WP_Post $post ): array {
			if (
				$post->post_type !== $this->post_type
				|| ! isset( $actions['edit'] )
			) {
				return $actions;
			}

			// Change the URL of the Edit Course link.

			if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
				$parser = new WP_HTML_Tag_Processor(
					Cast::to_string( $actions['edit'] )
				);
				$parser->next_tag( 'a' ); // @phpstan-ignore-line -- next_tag() also expects a string (it's a bug in the stub).

				$edit_url = $parser->get_attribute( 'href' );

				if ( ! empty( $edit_url ) ) {
					$parser->set_attribute( 'href', add_query_arg( 'currentTab', 'post-body-content', $edit_url ) );

					$actions['edit'] = strval( $parser );
				}

				return $actions;
			}

			// Fallback to regex if the WP_HTML_Tag_Processor class is not available (WP < 6.2.0).

			// href with single or double quotes.
			$href_regex = '/href=[\'"]([^\'"]+)[\'"]/';
			$edit_tag   = Cast::to_string( $actions['edit'] );

			$edit_url = preg_match( $href_regex, $edit_tag, $matches )
						? $matches[1]
						: '';

			if ( ! $edit_url ) {
				return $actions;
			}

			$actions['edit'] = preg_replace(
				$href_regex,
				'href="' . add_query_arg( 'currentTab', 'post-body-content', $edit_url ) . '"',
				$edit_tag
			);

			return $actions;
		}

		/**
		 * Common function to check if we are editing a correct post type.
		 *
		 * @since 2.6.0
		 *
		 * @param mixed $edit_post WP_Post object or post type string.
		 *
		 * @return boolean true is correct, else false.
		 */
		protected function post_type_check( $edit_post = null ) {
			global $typenow;

			if ( ! empty( $edit_post ) ) {
				if ( ( is_a( $edit_post, 'WP_Post' ) ) && ( $this->post_type === $edit_post->post_type ) ) {
					return true;
				} elseif ( ( is_string( $edit_post ) ) && ( $this->post_type === $edit_post ) ) {
					return true;
				}
			} elseif ( ( ! empty( $typenow ) ) && ( $typenow === $this->post_type ) ) {
				return true;
			}

			return false;
		}

		/**
		 * On Load handler function for this post type edit.
		 * This function is called by a WP action when the admin
		 * page 'post.php' or 'post-new.php' are loaded.
		 *
		 * @since 2.6.0
		 */
		public function on_load() {
			global $sfwd_lms;
			global $learndash_assets_loaded;

			if ( $this->post_type_check() ) {

				/**
				 * Filters post metaboxes init settings.
				 *
				 * The Dynamic part `$post_type` refers to the post type slug.
				 *
				 * @since 3.0.0
				 *
				 * @param array  $metaboxes Common array set to contain the metaboxes shown on the post edit screen.
				 */
				$this->_metaboxes = apply_filters( 'learndash_post_settings_metaboxes_init_' . $this->post_type, $this->_metaboxes );

				if ( ( isset( $_GET['post'] ) ) && ( ! empty( $_GET['post'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$this->post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}

				if ( ( ! empty( $this->_metaboxes ) ) && ( ! empty( $this->post_id ) ) ) {

					$sfwd_lms_instance_fields = $sfwd_lms->get_post_args_section( $this->post_type, 'fields' );
					if ( ( is_array( $sfwd_lms_instance_fields ) ) && ( ! empty( $sfwd_lms_instance_fields ) ) ) {
						foreach ( $this->_metaboxes as $_metaboxes_instance ) {
							$sfwd_lms_instance_fields = $_metaboxes_instance->check_legacy_metabox_fields( $sfwd_lms_instance_fields );
						}
					}

					if ( empty( $sfwd_lms_instance_fields ) ) {
						global $wp_meta_boxes;

						if ( isset( $wp_meta_boxes[ $this->post_type ] ) ) {
							foreach ( $wp_meta_boxes[ $this->post_type ] as &$m_sections ) {
								if ( ! empty( $m_sections ) ) {
									foreach ( $m_sections as $m_priority => &$m_boxes ) {
										if ( isset( $m_boxes[ $this->post_type ] ) ) {
											unset( $m_boxes[ $this->post_type ] );
										}
									}
								}
							}
						}
					} else {
						$screen = get_current_screen();
						global $learndash_metaboxes;
						if ( ! isset( $learndash_metaboxes[ $screen->id ] ) ) {
							$learndash_metaboxes[ $screen->id ] = array();
						}
						$learndash_metaboxes[ $screen->id ][ $screen->id ] = $screen->id;
					}
				}

				if ( ! isset( $learndash_assets_loaded['styles']['learndash-admin-binary-selector-script'] ) ) {
					wp_enqueue_script(
						'learndash-admin-binary-selector-script',
						LEARNDASH_LMS_PLUGIN_URL . 'assets/js/learndash-admin-binary-selector' . learndash_min_asset() . '.js',
						array( 'jquery' ),
						LEARNDASH_SCRIPT_VERSION_TOKEN,
						true
					);
					$learndash_assets_loaded['styles']['learndash-admin-binary-selector-script'] = __FUNCTION__;
				}

				if ( ! isset( $learndash_assets_loaded['styles']['learndash-admin-binary-selector-style'] ) ) {
					wp_enqueue_style(
						'learndash-admin-binary-selector-style',
						LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-binary-selector' . learndash_min_asset() . '.css',
						array(),
						LEARNDASH_SCRIPT_VERSION_TOKEN
					);
					wp_style_add_data( 'learndash-admin-binary-selector-style', 'rtl', 'replace' );
					$learndash_assets_loaded['styles']['learndash-admin-binary-selector-style'] = __FUNCTION__;
				}

				learndash_admin_settings_page_assets();

				if ( ! isset( $learndash_assets_loaded['styles']['learndash-admin-style'] ) ) {
					wp_enqueue_style(
						'learndash-admin-style',
						LEARNDASH_LMS_PLUGIN_URL . 'assets/css/learndash-admin-style' . learndash_min_asset() . '.css',
						array(),
						LEARNDASH_SCRIPT_VERSION_TOKEN
					);
					wp_style_add_data( 'learndash-admin-style', 'rtl', 'replace' );
					$learndash_assets_loaded['styles']['learndash-admin-style'] = __FUNCTION__;
				}

				$filepath = SFWD_LMS::get_template( 'learndash_pager.css', null, null, true );
				if ( ( ! empty( $filepath ) ) && ( ! isset( $learndash_assets_loaded['styles']['learndash_pager_css'] ) ) ) {
					wp_enqueue_style(
						'learndash_pager_css',
						learndash_template_url_from_path( $filepath ),
						array(),
						LEARNDASH_SCRIPT_VERSION_TOKEN
					);
					wp_style_add_data( 'learndash_pager_css', 'rtl', 'replace' );
					$learndash_assets_loaded['styles']['learndash_pager_css'] = __FUNCTION__;
				}

				$filepath = SFWD_LMS::get_template( 'learndash_pager.js', null, null, true );
				if ( ( ! empty( $filepath ) ) && ( ! isset( $learndash_assets_loaded['scripts']['learndash_pager_js'] ) ) ) {
					wp_enqueue_script(
						'learndash_pager_js',
						learndash_template_url_from_path( $filepath ),
						array( 'jquery' ),
						LEARNDASH_SCRIPT_VERSION_TOKEN,
						true
					);
					$learndash_assets_loaded['scripts']['learndash_pager_js'] = __FUNCTION__;
				}

				if ( isset( $_GET['ld_reset_metaboxes'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					delete_user_meta( get_current_user_id(), 'closedpostboxes_' . $this->post_type );
					delete_user_meta( get_current_user_id(), 'metaboxhidden_' . $this->post_type );
					delete_user_meta( get_current_user_id(), 'meta-box-order_' . $this->post_type );
				}

				// Add Metabox and hook for saving post metabox.
				add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ), 30, 2 );

				// Hide the content editor if we are not on the content tab.
				wp_add_inline_style(
					'learndash-admin-style',
					'body:not([data-active-tab="post-body-content"]) .edit-post-visual-editor { display: none }'
				);
			}
		}

		/**
		 * Check super global data.
		 *
		 * @since 2.6.0
		 *
		 * @param mixed $data Array data or null.
		 *
		 * @return array filtered data.
		 */
		protected function clear_request_data( $data = null ) {
			if ( null !== $data ) {
				$data = stripslashes_deep( $data );
			}

			return $data;
		}

		/**
		 * Called from WP at the start of the post edit <form> tag. Allows us
		 * to inject needed support fields.
		 *
		 * @since 3.0.0
		 *
		 * @param object $post WP Post object instance being edited.
		 */
		public function edit_form_top( $post ) {
			if ( $this->post_type_check() ) {
				$current_tab = '';
				if ( isset( $_GET['currentTab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$current_tab = sanitize_text_field( wp_unslash( $_GET['currentTab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
				echo '<input type="hidden" id="ld_post_edit_current_tab" name="ld_post_edit_current_tab" value="' . esc_attr( $current_tab ) . '" />';
			}
		}

		/**
		 * Called from WP when saving a post edit form. This filter allows us
		 * to interject a 'currentTab' query string variable.
		 *
		 * @since 3.0.0
		 *
		 * @param string  $location URL to redirect to after edit post processing.
		 * @param integer $post_id Post ID of item being edited.
		 *
		 * @return string  $location URL.
		 */
		public function redirect_post_location( $location = '', $post_id = 0 ) {
			if ( ( ! empty( $location ) ) && ( ! empty( $post_id ) ) ) {
				$post_type = get_post_type( $post_id );
				if ( $this->post_type_check( $post_type ) ) {
					if ( ( isset( $_POST['ld_post_edit_current_tab'] ) ) && ( ! empty( $_POST['ld_post_edit_current_tab'] ) ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$current_tab = sanitize_text_field( wp_unslash( $_POST['ld_post_edit_current_tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$location    = add_query_arg( 'currentTab', $current_tab, $location );
					}
				}
				return $location;
			}
			return '';
		}

		/**
		 * Override Gutenberg placeholder title shown when adding new post.
		 *
		 * @since 2.6.0
		 *
		 * @param string $placeholder_title Placeholder title to be shown. Default is 'Add title'.
		 * @param object $post WP_Post instance of post_type being added.
		 *
		 * @return string $placeholder_title.
		 */
		public function gutenberg_placeholder_enter_title_here( $placeholder_title = '', $post = null ) {
			if ( $this->post_type_check( $post ) ) {
				$post_type_object = get_post_type_object( $post->post_type );

				$placeholder_title = sprintf(
					// translators: placeholders: Post Type singular label.
					esc_html_x( 'Add %s title', 'placeholders: Post Type singular label', 'learndash' ),
					$post_type_object->labels->singular_name
				);
			}

			// Always return $placeholder_text.
			return $placeholder_title;
		}
		/**
		 * Override Gutenberg placeholder text shown when adding new post.
		 *
		 * @since 2.6.0
		 *
		 * @param string $placeholder_text Placeholder text to be shown. Default is 'Write your story'.
		 * @param object $post WP_Post instance of post_type being added.
		 *
		 * @return string $placeholder_text.
		 */
		public function gutenberg_placeholder_write_your_story( $placeholder_text = ' ', $post = null ) {
			if ( $this->post_type_check( $post ) ) {
				$post_type_object = get_post_type_object( $post->post_type );

				$placeholder_text = sprintf(
					// translators: placeholders: Post Type singular label.
					esc_html_x( 'Add %s content.', 'placeholders: Post Type singular label', 'learndash' ),
					$post_type_object->labels->singular_name
				);
			}

			// Always return $placeholder_text.
			return $placeholder_text;
		}

		/**
		 * Save Question handler function.
		 *
		 * @since 2.6.0
		 *
		 * @param integer $post_id Post ID Question being edited.
		 * @param object  $post WP_Post Question being edited.
		 * @param boolean $update If update true, else false.
		 */
		public function save_post( $post_id = 0, $post = null, $update = false ) {
			if ( ! $this->post_type_check() ) {
				return false;
			}

			// If this is just a revision, don't send the email.
			if ( wp_is_post_revision( $post_id ) ) {
				return false;
			}

			if ( ! isset( $_POST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return false;
			}

			if ( $_POST['post_type'] !== $this->post_type ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				return false;
			}

			// Check permissions.
			if ( learndash_get_post_type_slug( 'group' ) === $post->post_type ) {
				if ( ! current_user_can( 'edit_group', $post_id ) ) {
					return false;
				}
			} elseif ( ! current_user_can( 'edit_course', $post_id ) ) {
				return false;
			}

			// Remove our save_Post hook to prevent recursive save loops.
			remove_action( 'save_post', array( $this, 'save_post' ), 50, 3 );

			// Clear the oEmbed post cache.
			$this->post_clear_oembed_cache( $post_id );

			return true;
		}

		/**
		 * Edit Post handler function.
		 *
		 * @since 3.2.3
		 *
		 * @param integer $post_id Post ID being edited.
		 * @param object  $post    WP_Post being edited.
		 */
		public function edit_post_post_type( $post_id = 0, $post = null ) {
		}

		/**
		 * Filter post_parent before update/insert. Ensure the post_parent field is zero for course post types.
		 *
		 * @since 3.1.0
		 *
		 * @param  integer $post_parent Post Parent post ID.
		 * @param  integer $post_id     Post ID being edited.
		 * @param  array   $new_postarr Array of updated POST fields to be saved.
		 * @param  array   $postarr     Array of previous POST fields to be saved.
		 *
		 * @return integer $post_parent
		 */
		public function filter_post_parent( $post_parent = 0, $post_id = 0, $new_postarr = array(), $postarr = array() ) {
			if ( ( ! empty( $post_parent ) ) && ( isset( $new_postarr['post_type'] ) ) && ( $this->post_type === $new_postarr['post_type'] ) ) {
				if ( in_array( $this->post_type, learndash_get_post_types( 'course' ), true ) ) {
					$post_parent = 0;
				}
			}
			return $post_parent;
		}

		/**
		 * Register metaboxes for course Associated Content
		 *
		 * @since 2.6.0
		 *
		 * @param string $post_type Post Type being edited.
		 * @param object $post      WP_Post Post being edited.
		 */
		public function add_metaboxes( $post_type = '', $post = null ) {
			if ( $this->post_type_check( $post_type ) ) {

				// If we are showing a course or related 'step' we show the Assoc Content metabox.
				$course_post_types = LDLMS_Post_Types::get_post_types( 'course' );
				if ( ( ! empty( $course_post_types ) ) && ( in_array( $this->post_type, $course_post_types, true ) ) ) {
					add_meta_box(
						'learndash_course_navigation_admin_meta',
						esc_html__( 'Associated Content', 'learndash' ),
						'learndash_course_navigation_admin_box_content',
						$this->post_type,
						'side',
						'high'
					);
				}

				if ( ( true === learndash_is_data_upgrade_quiz_questions_updated() ) && ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Quizzes_Builder', 'enabled' ) === 'yes' ) ) {

					// If we are showing a Quiz or Question we show the Quiz Questions metabox.
					$quiz_post_types = LDLMS_Post_Types::get_post_types( 'quiz' );
					if ( ( ! empty( $quiz_post_types ) ) && ( in_array( $this->post_type, $quiz_post_types, true ) ) ) {

						add_meta_box(
							'learndash_admin_quiz_navigation',
							sprintf(
								// translators: placeholders: Quiz, Questions.
								esc_html_x( '%1$s %2$s %3$s', 'placeholders: Quiz, Questions', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' ),
								LearnDash_Custom_Label::get_label( 'questions' ),
								'<span class="questions-count"></span>'
							),
							'learndash_quiz_navigation_admin_box_content',
							$this->post_type,
							'side',
							'high'
						);
					}
				}
			}
		}

		/**
		 * Clear the Post oEmbed cache.
		 *
		 * This is mostly needed for Lessons and Topics when
		 * using the Video Progression logic. But we are
		 * supporting this on all LD post types.
		 *
		 * @since 3.1.4
		 *
		 * @param integer $post_id ID of Post to clear cache for.
		 */
		public function post_clear_oembed_cache( $post_id = 0 ) {
			if ( ! empty( $post_id ) ) {
				$wp_embed = new WP_Embed();
				$wp_embed->delete_oembed_caches( $post_id );
			}
		}

		// End of functions.
	}
}

// Include the LearnDash table listing files here.
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-course-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-lesson-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-topic-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-quiz-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-question-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-essay-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-group-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-assignment-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-exam-edit.php';
require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/admin/classes-posts-edits/class-learndash-admin-coupon-edit.php';
