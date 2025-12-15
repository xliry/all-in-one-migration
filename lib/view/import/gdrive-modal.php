<?php
/**
 * Copyright (C) 2014-2018 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */
?>

<div class="ai1wm-gdrive-import-dialog" style="display: none;">
	<div class="ai1wm-overlay"></div>
	<div class="ai1wm-modal-container">
		<div class="ai1wm-modal-content">
			<h2>
				<i class="ai1wm-icon-google-drive"></i>
				<?php _e( 'Import from Google Drive', AI1WM_PLUGIN_NAME ); ?>
			</h2>

			<div class="ai1wm-field">
				<label for="ai1wm-gdrive-url">
					<?php _e( 'Google Drive File URL', AI1WM_PLUGIN_NAME ); ?>
				</label>
				<input
					type="text"
					id="ai1wm-gdrive-url"
					class="ai1wm-gdrive-url"
					placeholder="<?php _e( 'Paste Google Drive shareable link here...', AI1WM_PLUGIN_NAME ); ?>"
				/>
				<small class="ai1wm-field-help">
					<?php _e( 'Example: https://drive.google.com/file/d/FILE_ID/view', AI1WM_PLUGIN_NAME ); ?>
				</small>
			</div>

			<div class="ai1wm-field">
				<div class="ai1wm-message ai1wm-info-message">
					<p>
						<strong><?php _e( 'Important:', AI1WM_PLUGIN_NAME ); ?></strong><br />
						<?php _e( 'Make sure your file is shared as "Anyone with the link" with at least "Viewer" permission.', AI1WM_PLUGIN_NAME ); ?><br />
						<?php
							printf(
								__( 'You need to configure your Google Drive API key in <a href="%s">Settings</a> before importing.', AI1WM_PLUGIN_NAME ),
								admin_url( 'admin.php?page=ai1wm_settings' )
							);
						?>
					</p>
				</div>
			</div>

			<div class="ai1wm-field" id="ai1wm-gdrive-messages" style="display: none;"></div>

			<div class="ai1wm-field">
				<div class="ai1wm-buttons">
					<a href="#" id="ai1wm-gdrive-cancel" class="ai1wm-gdrive-cancel">
						<?php _e( 'Cancel', AI1WM_PLUGIN_NAME ); ?>
					</a>
					<button type="submit" id="ai1wm-gdrive-submit" class="ai1wm-button-green">
						<i class="ai1wm-icon-publish"></i>
						<?php _e( 'Import from Google Drive', AI1WM_PLUGIN_NAME ); ?>
					</button>
					<span class="spinner"></span>
					<div class="ai1wm-clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>
