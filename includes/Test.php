<?php


use Automattic\Jetpack\Connection\Manager;


/**
 * Main class for Test
 */
final class Test {

	public static function init() {

		$manager           = new Manager( 'test' );
		$active            = $manager->is_active() ? 'true' : 'false';
		$blog_token        = $manager->get_access_token();
		$blog_token_secret = $blog_token ? $blog_token->secret : '';
		$user_token        = $manager->get_access_token( get_current_user_id() );
		$user_token_secret = $user_token ? $user_token->secret : '';

		// Debugging
		error_log( 'init: test connection plugin' );
		error_log( 'connection active: ' . $active );
		error_log( 'blog token: ' . $blog_token_secret );
		error_log( 'user token: ' . $user_token_secret );
		error_log( $redirect = admin_url('admin.php?page=test-admin-page') );

		// Create test admin page
		add_action( 'admin_menu', [ 'Test', 'my_admin_menu' ] );

		// Register site and user
		add_action( 'admin_init', [ 'Test', 'do_stuff' ] );
	}

	public static function my_admin_menu() {
		add_menu_page(
			'Test Connection',
			'Test Connection',
			'manage_options',
			'test-admin-page',
			[ 'Test', 'test_admin_page' ],
			'dashicons-tickets',
			6
		);
	}

	public static function test_admin_page(){

		$manager    = new Manager( 'test' );
		$blog_token = $manager->get_access_token();
		$user_token = $manager->get_access_token( get_current_user_id() );


		?>
		<div class="wrap">
			<h2>Connect to Jetpack</h2>

			<?php if ( $blog_token ) { ?>
				<p>Awesome! Your site is connected!</p>
			<?php } ?>

			<?php if ( $user_token ) { ?>
				<p>Awesome! You are connected as an authenticated user!</p>
			<?php } ?>

			<?php if ( ! $blog_token || ! $user_token ) { ?>
				<a href="<?php echo wp_nonce_url( add_query_arg( [ 'connect' => '1' ] ), 'connect' );?>">Connect</a>
			<?php } ?>

			<?php if ( $blog_token && $user_token ) { ?>
				<a href="<?php echo wp_nonce_url( add_query_arg( [ 'disconnect' => '1' ] ), 'disconnect' );?>">Disconnect</a>
			<?php } ?>

		</div>
		<?php
	}

	public static function do_stuff() {

		if ( isset( $_GET['connect'] ) && check_admin_referer( 'connect' ) ) {

			$manager = new Manager( 'test' );

			$redirect = admin_url('admin.php?page=test-admin-page');

			// Mark the plugin connection as enabled, in case it was disabled earlier.
			$manager->enable_plugin();

			error_log( 'after enabled' );

			// Register the site to wp.com.
			if ( ! $manager->is_registered() ) {

				$result = $manager->register();

				if ( is_wp_error( $result ) ) {
					error_log( var_export( $result , true ) );
				}

				error_log( var_export( $manager->get_access_token() , true ) );
			}

			//???
			add_filter( 'jetpack_use_iframe_authorization_flow', '__return_false' );

			error_log($redirect);

			$auth_url = $manager->get_authorization_url( null, $redirect );

			error_log( $auth_url );

			// User connection
			$didit = wp_redirect( $auth_url ); // wp_safe_redirect wasn't working

			error_log( var_export( $didit , true ) );

			exit;
		}

		if ( isset( $_GET['disconnect'] ) && check_admin_referer( 'disconnect' ) ) {

			$manager = new Manager( 'test' );

			$manager->remove_connection();

			$redirect = admin_url('admin.php?page=test-admin-page');

			wp_safe_redirect( $redirect );

			exit;
		}

		return;
	}
}
