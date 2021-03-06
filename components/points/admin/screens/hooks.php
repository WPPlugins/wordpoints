<?php

/**
 * Points Hooks administration panel.
 *
 * @package WordPoints\Points\Admin
 * @since 1.0.0
 */

// Get all points types.
$points_types = wordpoints_get_points_types();

// These messages/errors are used upon redirection from the non-JS version.
$messages = array(
	__( 'Changes saved.', 'wordpoints' ),
);

$errors = array(
	__( 'Error while saving.', 'wordpoints' ),
	__( 'Error in displaying the hooks settings form.', 'wordpoints' ),
	'', // Back-compat for pre-2.1.0.
	__( 'Error while deleting.', 'wordpoints' ),
);

if ( is_network_admin() ) {
	$title = _x( 'Network Points Hooks', 'page title', 'wordpoints' );
} else {
	$title = _x( 'Points Hooks', 'page title', 'wordpoints' );
}

?>

<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<?php

	if ( empty( $points_types ) && ! current_user_can( 'manage_wordpoints_points_types' ) ) {

		wordpoints_show_admin_error( esc_html__( 'No points types have been created yet. Only network administrators can create points types.', 'wordpoints' ) );

		echo '</div>';
		return;
	}

	if ( isset( $_GET['message'] ) && isset( $messages[ (int) $_GET['message'] ] ) ) { // WPCS: CSRF OK.

		wordpoints_show_admin_message(
			esc_html( $messages[ (int) $_GET['message'] ] ) // WPCS: CSRF OK.
			, 'success'
			, array( 'dismissible' => true )
		);

	} elseif ( isset( $_GET['error'] ) && isset( $errors[ (int) $_GET['error'] ] ) ) { // WPCS: CSRF OK.

		wordpoints_show_admin_error(
			esc_html( $errors[ (int) $_GET['error'] ] ) // WPCS: CSRF OK.
			, array( 'dismissible' => true )
		);
	}

	if ( is_network_admin() && current_user_can( 'manage_network_wordpoints_points_hooks' ) ) {

		// Display network wide hooks.
		WordPoints_Points_Hooks::set_network_mode( true );
	}

	/**
	 * Top of points hooks admin screen.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_points_hooks_head' );

	?>

	<div class="hook-liquid-left hook-liquid-left">
		<div id="hooks-left">
			<div id="available-hooks" class="hooks-holder-wrap hooks-holder-wrap">
				<div class="points-type-name">
					<div class="points-type-name-arrow"><br /></div>
					<h2><?php esc_html_e( 'Available Hooks', 'wordpoints' ); ?> <span id="removing-hook"><?php echo esc_html_x( 'Deactivate', 'removing-hook', 'wordpoints' ); ?> <span></span></span></h2>
				</div>
				<div class="hook-holder hook-holder">
					<p class="description"><?php esc_html_e( 'Drag hooks from here to a points type on the right to activate them. Drag hooks back here to deactivate them and delete their settings.', 'wordpoints' ); ?></p>
					<div id="hook-list">
						<?php WordPoints_Points_Hooks::list_hooks(); ?>
					</div>
					<br class="clear" />
				</div>
				<br class="clear" />
			</div>

			<div class="hooks-holder-wrap inactive-points-type">
				<div class="points-type-name">
					<div class="points-type-name-arrow"><br /></div>
					<h2><?php esc_html_e( 'Inactive Hooks', 'wordpoints' ); ?>
						<span class="spinner"></span>
					</h2>
				</div>
				<div class="hook-holder inactive hook-holder">
					<div id="_inactive_hooks" class="hooks-sortables">
						<div class="points-type-description">
							<p class="description">
								<?php esc_html_e( 'Drag hooks here to remove them from the points type but keep their settings.', 'wordpoints' ); ?>
							</p>
						</div>
						<?php WordPoints_Points_Hooks::list_by_points_type( '_inactive_hooks' ); ?>
					</div>
					<div class="clear"></div>
				</div>
			</div>

		</div>
	</div>

	<div class="hook-liquid-right">
		<div id="hooks-right">

			<?php

			$i = 0;

			foreach ( $points_types as $slug => $points_type ) {

				$wrap_class = 'hooks-holder-wrap';
				if ( ! empty( $points_type['class'] ) ) {
					$wrap_class .= ' points-type-' . $points_type['class'];
				}

				if ( $i ) {
					$wrap_class .= ' closed';
				}

				?>

				<div class="<?php echo esc_attr( $wrap_class ); ?>">
					<div class="points-type-name">
						<div class="points-type-name-arrow"><br /></div>
						<h2><?php echo esc_html( $points_type['name'] ); ?><span class="spinner"></span></h2>
					</div>
					<div id="<?php echo esc_attr( $slug ); ?>" class="hooks-sortables">

						<?php

						if (
							get_site_option( 'wordpoints_disabled_points_hooks_edit_points_types' )
							&& current_user_can( 'manage_wordpoints_points_types' )
						) {
							?>
							<div class="notice notice-info inline notice-alt">
								<p>
									<?php

									echo wp_kses_data(
										sprintf(
											// translators: URL of Points Types admin screen.
											__( 'You can edit this points type&#8219;s settings on the <a href="%s">Points Types screen</a>.', 'wordpoints' )
											, esc_attr(
												esc_url(
													self_admin_url(
														'admin.php?page=wordpoints_points_types&tab='
															. $slug
													)
												)
											)
										)
									);

									?>
								</p>
							</div>
							<?php
						}

						WordPoints_Points_Hooks::list_by_points_type( $slug );

						?>

					</div>
				</div>

				<?php

				$i++;

			} // End foreach ( $points_types ).

			if (
				get_site_option( 'wordpoints_disabled_points_hooks_edit_points_types' )
				&& current_user_can( 'manage_wordpoints_points_types' )
			) {

				?>

				<div class="hooks-holder-wrap new-points-type <?php echo ( $i > 0 ) ? 'closed' : ''; ?>">
					<div class="points-type-name">
						<div class="points-type-name-arrow"><br /></div>
						<h2><?php esc_html_e( 'Add New Points Type', 'wordpoints' ); ?><span class="spinner"></span></h2>
					</div>
					<div class="wordpoints-points-add-new hooks-sortables hook">
						<div class="notice notice-info inline notice-alt">
							<p>
								<?php

								echo wp_kses_data(
									sprintf(
										// translators: URL of Points Types admin screen.
										__( 'You can create new points types on the <a href="%s">Points Types screen</a>.', 'wordpoints' )
										, esc_attr(
											esc_url(
												self_admin_url(
													'admin.php?page=wordpoints_points_types&tab=add-new'
												)
											)
										)
									)
								);

								?>
							</p>
						</div>
					</div>
				</div>

				<?php

			} // End if ( user can create points types ).

			?>

		</div>
	</div>
	<form method="post">
		<?php

		if ( WordPoints_Points_Hooks::get_network_mode() ) {
			$field = 'save-network-wordpoints-points-hooks';
		} else {
			$field = 'save-wordpoints-points-hooks';
		}

		wp_nonce_field( $field, '_wpnonce_hooks', false );

		?>
	</form>

	<?php

	/**
	 * Bottom of points hooks admin screen.
	 *
	 * @since 1.0.0
	 */
	do_action( 'wordpoints_admin_points_hooks_foot' );

	?>

	<br class="clear" />

	<div class="hooks-chooser">
		<h3><?php esc_html_e( 'Choose a points type:', 'wordpoints' ); ?></h3>
		<ul class="hooks-chooser-points-types"></ul>
		<div class="hooks-chooser-actions">
			<button class="button-secondary"><?php esc_html_e( 'Cancel', 'wordpoints' ); ?></button>
			<button class="button-primary"><?php esc_html_e( 'Add Hook', 'wordpoints' ); ?></button>
		</div>
	</div>
</div>
