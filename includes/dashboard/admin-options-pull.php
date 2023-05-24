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
$admin_dashboard_name = 'pull';
$nonce_prefix = 'toolboxsync';

$remote_site = get_option( 'tsync_remotesite' );
if (!$remote_site) $remote_site = 'https://';
?>
<div class="jq-tab-content" data-tab="<?php echo $admin_dashboard_name; ?>">
	<form id="<?php echo Dashboard::prefix();?>-<?php echo $admin_dashboard_name; ?>" action="<?php echo Dashboard::render_form_action( $admin_dashboard_name ); ?>" method="post">
			<h3><?php _e( 'Pull Posts from Remote' , 'toolbox-sync' );?></h3>
			<table class="form-table">
			<tr valign="top">
					<td colspan="2">
						<?php
								echo Dashboard::input(
													'dropdown' ,
													[
														'id' => 'toolboxsync-posttype-pull',
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
														'id' => 'toolboxsync-getremote-pull',
														'label' => 'Check Synceable Status'
													] 
								);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2" id="tsync-actions-pull">
					</td>
				</tr>
			</table>
	</form>
</div>
<template class="tsync-de_select_all-pull">
	<tr>
		<td colspan="2">
			<input type="checkbox" name="de_select_all-pull" id="de_select_all-pull"><label for="de_select_all-pull"></label>
		</td>
	</tr>
</template>
<template class="tsync-row-pull">
	<tr>
		<td class="remote-id"><input type="checkbox" name="pull[]" value="" id="pull_{id}"><label for="pull_{id}"></label></td>
		<td class="local-id"></td>
	</tr>
</template>
<template class="tsync-button-pull">
	<button id="pull_posts" class="button-primary">PULL SELECTED POSTS</button>
</template>
