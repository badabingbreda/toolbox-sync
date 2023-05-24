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

$remote_site = get_option( 'tsync_remotesite' );
if (!$remote_site) $remote_site = 'https://';
?>
<div class="jq-tab-content" data-tab="<?php echo $admin_dashboard_name; ?>">
	<form id="<?php echo Dashboard::prefix();?>-<?php echo $admin_dashboard_name; ?>" action="<?php echo Dashboard::render_form_action( $admin_dashboard_name ); ?>" method="post">
			<h3><?php _e( 'Push Posts to Remote' , 'toolbox-sync' );?></h3>
			<table class="form-table">
			<tr valign="top">
					<td colspan="2">
						<?php
								echo Dashboard::input(
													'dropdown' ,
													[
														'id' => 'toolboxsync-posttype-push',
														'label' => 'Select Post Type',
														'value' => 'fl-theme-layout',
														'options' => apply_filters( 'toolboxsync/push_post_types' , [] )
													] 
								);
						?>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<?php
								echo Dashboard::input(
													'button' ,
													[
														'id' => 'toolboxsync-getremote-push',
														'label' => 'Check Synceable Status'
													] 
								);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2" id="tsync-actions-push">
					</td>
				</tr>
			</table>
	</form>
</div>
<template class="tsync-de_select_all-push">
	<tr>
		<td colspan="2">
			<input type="checkbox" name="de_select_all" id="de_select_all-push"><label for="de_select_all-push"></label>
		</td>
	</tr>
</template>
<template class="tsync-row-push">
	<tr>
		<td class="local-id"><input type="checkbox" name="push[]" value="" id="push_{id}"><label for="push_{id}"></label></td>
		<td class="remote-id"></td>
	</tr>
</template>
<template class="tsync-button-push">
	<button id="push_posts" class="button-primary">PUSH SELECTED POSTS</button>
</template>
