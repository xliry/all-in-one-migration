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

<div class="ai1wm-gdrive-v2-import-dialog" style="display: none;">
	<div class="ai1wm-overlay"></div>
	<div class="ai1wm-modal-container">
		<div class="ai1wm-modal-content">
			<h2>
				<i class="ai1wm-icon-google-drive"></i>
				<?php _e( 'Import from Google Drive V2 (No API Key)', AI1WM_PLUGIN_NAME ); ?>
			</h2>

			<div class="ai1wm-field">
				<label for="ai1wm-gdrive-v2-url">
					<?php _e( 'Google Drive File URL', AI1WM_PLUGIN_NAME ); ?>
				</label>
				<input
					type="text"
					id="ai1wm-gdrive-v2-url"
					class="ai1wm-gdrive-v2-url"
					placeholder="<?php _e( 'Paste Google Drive shareable link here...', AI1WM_PLUGIN_NAME ); ?>"
				/>
				<small class="ai1wm-field-help">
					<?php _e( 'Example: https://drive.google.com/file/d/FILE_ID/view', AI1WM_PLUGIN_NAME ); ?>
				</small>
			</div>

			<div class="ai1wm-field">
				<div class="ai1wm-message ai1wm-info-message">
					<p>
						<strong><?php _e( 'No API Key Required!', AI1WM_PLUGIN_NAME ); ?></strong><br />
						<?php _e( 'This version downloads directly from Google Drive without needing an API key.', AI1WM_PLUGIN_NAME ); ?><br />
						<?php _e( 'Make sure your file is shared as "Anyone with the link" with at least "Viewer" permission.', AI1WM_PLUGIN_NAME ); ?>
					</p>
				</div>
			</div>

			<div class="ai1wm-field" id="ai1wm-gdrive-v2-messages" style="display: none;"></div>

			<div class="ai1wm-field">
				<div class="ai1wm-buttons">
					<a href="#" id="ai1wm-gdrive-v2-cancel" class="ai1wm-gdrive-v2-cancel">
						<?php _e( 'Cancel', AI1WM_PLUGIN_NAME ); ?>
					</a>
					<button type="submit" id="ai1wm-gdrive-v2-submit" class="ai1wm-button-green">
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
