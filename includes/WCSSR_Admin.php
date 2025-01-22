<?php

namespace WCSSR;

/**
 * Class WCSSR_Admin
 *
 * Handles the administrative functionalities for the Role-Based Discounts plugin.
 * Provides methods to manage discount rules, display the admin page, and handle
 * save/delete operations for rules.
 */
class WCSSR_Admin {
	/**
	 * Option name used to store discount rules in the WordPress database.
	 *
	 * @var string $option_name
	 */
	private $option_name = 'wcssr_role_discount_rules';

	/**
	 * Constructor to initialize the admin class.
	 *
	 * Hooks the required actions for adding the admin menu and handling form submissions.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wcssr_add_plugin_admin_menu' ) );
		add_action( 'admin_post_save_discount_rule', array( $this, 'wcssr_save_discount_rule' ) );
		add_action( 'admin_post_delete_discount_rule', array( $this, 'wcssr_delete_discount_rule' ) );
	}
	/**
	 * Adds a submenu for Role-Based Discounts under the WooCommerce menu.
	 */
	public function wcssr_add_plugin_admin_menu() {
		add_submenu_page(
			'woocommerce',
			'Role Based Discounts',
			'Role Discounts',
			'manage_options',
			'role-based-discounts',
			array( $this, 'wcssr_admin_page' )
		);
	}
	/**
	 * Retrieves the saved discount rules from the WordPress database.
	 *
	 * @return array An array of discount rules, or an empty array if no rules exist.
	 */
	private function wcssr_get_discount_rules() {
		return get_option( $this->option_name, array() );
	}

	/**
	 * Displays the admin page for discount rules.
	 *
	 * Includes:
	 * - A form for adding or editing discount rules.
	 * - A table displaying all existing discount rules.
	 */
	public function wcssr_admin_page() {
		settings_errors( 'role_based_discounts' );
		$roles          = wp_roles()->get_names();
		$discount_rules = $this->wcssr_get_discount_rules();
		$edit_id        = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
		$rule_to_edit   = null;
		if ( $edit_id ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_discount_rule_nonce' ) ) {
				wp_die( 'Nonce verification failed for edit action.' );
			}

			if ( isset( $discount_rules[ $edit_id ] ) ) {
				$rule_to_edit = $discount_rules[ $edit_id ];
			}
		}

		?>
		<div class="wrap">
			<h1><?php echo $edit_id ? 'Edit Discount Rule' : 'Role Based Discounts'; ?></h1>
			<div class="card" style="max-width: 600px; padding: 20px; margin-bottom: 20px;">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="save_discount_rule">
					<?php wp_nonce_field( 'save_discount_rule_nonce' ); ?>
					<?php if ( $edit_id ) : ?>
						<input type="hidden" name="rule_id" value="<?php echo esc_attr( $edit_id ); ?>">
					<?php endif; ?>

					<table class="form-table">
						<tr>
							<th><label for="wcssr_role_name">User Role</label></th>
							<td>
								<select name="wcssr_role_name" id="wcssr_role_name" required>
									<option value="">Select Role</option>
									<?php foreach ( $roles as $role_id => $wcssr_role_name ) : ?>
										<option value="<?php echo esc_attr( $role_id ); ?>"
											<?php selected( $rule_to_edit && $rule_to_edit['wcssr_role_name'] === $role_id ); ?>>
											<?php echo esc_html( $wcssr_role_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="wcssr_discount_percentage">Discount (%)</label></th>
							<td>
								<input type="number"
										name="wcssr_discount_percentage"
										id="wcssr_discount_percentage"
										value="<?php echo $rule_to_edit ? esc_attr( $rule_to_edit['wcssr_discount_percentage'] ) : ''; ?>"
										min="0"
										max="100"
										step="0.01"
										required
								/>
							</td>
						</tr>

					</table>

					<?php submit_button( $edit_id ? 'Update Rule' : 'Add Rule' ); ?>
				</form>
			</div>


			<table class="wp-list-table widefat fixed striped">
				<thead>
				<tr>
					<th>Role</th>
					<th>Discount</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if ( ! empty( $discount_rules ) ) : ?>
					<?php foreach ( $discount_rules as $rule_id => $rule ) : ?>
						<tr>
							<td><?php echo esc_html( $roles[ $rule['wcssr_role_name'] ] ); ?></td>
							<td><?php echo esc_html( $rule['wcssr_discount_percentage'] ); ?>%</td>
							<td>
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'edit'     => $rule_id,
											'_wpnonce' => wp_create_nonce( 'edit_discount_rule_nonce' ),
										),
										admin_url( 'admin.php?page=role-based-discounts' )
									)
								);
								?>
									" class="button button-small">Edit</a>
								<form method="post"
										action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
										style="display:inline;">
									<input type="hidden" name="action" value="delete_discount_rule">
									<input type="hidden" name="rule_id" value="<?php echo esc_attr( $rule_id ); ?>">
									<?php wp_nonce_field( 'delete_discount_rule_nonce' ); ?>
									<button type="submit"
											class="button button-small button-link-delete"
											onclick="return confirm('Are you sure?');">
										Delete
									</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="3">No discount rules found.</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
	/**
	 * Handles the saving or updating of discount rules.
	 *
	 * Validates the user, checks the nonce, and saves the submitted rule to the database.
	 */
	public function wcssr_save_discount_rule() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		check_admin_referer( 'save_discount_rule_nonce' );
		$rule_id                   = isset( $_POST['rule_id'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) : uniqid( 'rule_' );
		$wcssr_role_name           = isset( $_POST['wcssr_role_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wcssr_role_name'] ) ) : '';
		$wcssr_discount_percentage = isset( $_POST['wcssr_discount_percentage'] ) ? floatval( wp_unslash( $_POST['wcssr_discount_percentage'] ) ) : 0;
		$discount_rules            = $this->wcssr_get_discount_rules();
		$duplicate_found           = false;
		foreach ( $discount_rules as $existing_rule_id => $existing_rule ) {
			if ( $existing_rule['wcssr_role_name'] === $wcssr_role_name && $existing_rule_id !== $rule_id ) {
				$duplicate_found = true;
				break;
			}
		}
		if ( ! $duplicate_found ) {
			$discount_rules[ $rule_id ] = array(
				'wcssr_role_name'           => $wcssr_role_name,
				'wcssr_discount_percentage' => $wcssr_discount_percentage,
			);
			update_option( $this->option_name, $discount_rules );
			if ( $duplicate_found ) {
				add_settings_error(
					'role_based_discounts',
					'duplicate_role',
					'A discount rule for this role already exists. The new rule has been added, but be aware that this role already has an existing rule.',
					'warning'
				);
			}
			wp_safe_redirect( admin_url( 'admin.php?page=role-based-discounts' ) );
			exit;
		} else {
			add_settings_error(
				'role_based_discounts',
				'duplicate_role',
				'A rule already exists for this role. Please update the existing rule or choose another role.',
				'error'
			);
			wp_safe_redirect( admin_url( 'admin.php?page=role-based-discounts' ) );
			exit;
		}
	}



	/**
	 * Handles the deletion of a discount rule.
	 *
	 * Validates the user, checks the nonce, and removes the specified rule from the database.
	 */
	public function wcssr_delete_discount_rule() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'delete_discount_rule_nonce' );

		$rule_id = isset( $_POST['rule_id'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) : '';

		if ( $rule_id ) {
			$discount_rules = $this->wcssr_get_discount_rules();
			if ( isset( $discount_rules[ $rule_id ] ) ) {
				unset( $discount_rules[ $rule_id ] );
				update_option( $this->option_name, $discount_rules );
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=role-based-discounts' ) );
		exit;
	}
}