<?php
/**
 * Dashboard Admin Options - Default
 *
 * @package {{plugin.name}}
 * @since 1.0.0
 * @author {{plugin.author}}
 * @link {{plugin.authoruri}}
 * @license {{plugin.license}}
 */

namespace ToolboxSync\Dashboard;
// use variable so we can't forget the replace the name somewhere important
$admin_dashboard_name = 'push';
$nonce_prefix = 'toolboxsync';

$remote_site = get_option( 'toolboxsync_remotesite' );
if (!$remote_site) $remote_site = 'https://';
?>
<div class="jq-tab-content" data-tab="<?php echo $admin_dashboard_name; ?>">
	<form id="<?php echo Dashboard::prefix();?>-<?php echo $admin_dashboard_name; ?>" action="<?php echo Dashboard::render_form_action( $admin_dashboard_name ); ?>" method="post">
			<h3><?php _e( 'Push Posts' , 'toolbox-sync' );?></h3>
			<table class="form-table">
				<tr valign="top">
					<td colspan="2">
						<?php
								echo Dashboard::input(
													'button' ,
													[
														'id' => 'toolboxsync-getremoteposts',
														'label' => 'Get Remote Posts'
													] );
						?>
					</td>
				</tr>
			</table>
		<p class="submit">
						<?php
								echo Dashboard::input(
													'submit' ,
													[
														'value' => __( 'Update Settings', 'toolbox-sync' ),
													] );
						?>
			<?php wp_nonce_field( $admin_dashboard_name, Dashboard::prefix()."-{$admin_dashboard_name}-nonce" ); ?>
		</p>
	</form>
</div>
