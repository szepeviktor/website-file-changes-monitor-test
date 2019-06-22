<?php
/**
 * Settings View.
 *
 * @package wfcm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Scan Frequencies.
 */
$frequency_options = apply_filters(
	'wfcm_file_changes_scan_frequency',
	array(
		'daily'   => __( 'Daily', 'website-file-changes-monitor' ),
		'weekly'  => __( 'Weekly', 'website-file-changes-monitor' ),
		'monthly' => __( 'Monthly', 'website-file-changes-monitor' ),
	)
);

// Scan hours option.
$scan_hours = array(
	'00' => __( '00:00', 'website-file-changes-monitor' ),
	'01' => __( '01:00', 'website-file-changes-monitor' ),
	'02' => __( '02:00', 'website-file-changes-monitor' ),
	'03' => __( '03:00', 'website-file-changes-monitor' ),
	'04' => __( '04:00', 'website-file-changes-monitor' ),
	'05' => __( '05:00', 'website-file-changes-monitor' ),
	'06' => __( '06:00', 'website-file-changes-monitor' ),
	'07' => __( '07:00', 'website-file-changes-monitor' ),
	'08' => __( '08:00', 'website-file-changes-monitor' ),
	'09' => __( '09:00', 'website-file-changes-monitor' ),
	'10' => __( '10:00', 'website-file-changes-monitor' ),
	'11' => __( '11:00', 'website-file-changes-monitor' ),
	'12' => __( '12:00', 'website-file-changes-monitor' ),
	'13' => __( '13:00', 'website-file-changes-monitor' ),
	'14' => __( '14:00', 'website-file-changes-monitor' ),
	'15' => __( '15:00', 'website-file-changes-monitor' ),
	'16' => __( '16:00', 'website-file-changes-monitor' ),
	'17' => __( '17:00', 'website-file-changes-monitor' ),
	'18' => __( '18:00', 'website-file-changes-monitor' ),
	'19' => __( '19:00', 'website-file-changes-monitor' ),
	'20' => __( '20:00', 'website-file-changes-monitor' ),
	'21' => __( '21:00', 'website-file-changes-monitor' ),
	'22' => __( '22:00', 'website-file-changes-monitor' ),
	'23' => __( '23:00', 'website-file-changes-monitor' ),
);

// Scan days option.
$scan_days = array(
	'1' => __( 'Monday', 'website-file-changes-monitor' ),
	'2' => __( 'Tuesday', 'website-file-changes-monitor' ),
	'3' => __( 'Wednesday', 'website-file-changes-monitor' ),
	'4' => __( 'Thursday', 'website-file-changes-monitor' ),
	'5' => __( 'Friday', 'website-file-changes-monitor' ),
	'6' => __( 'Saturday', 'website-file-changes-monitor' ),
	'7' => __( 'Sunday', 'website-file-changes-monitor' ),
);

// Scan date option.
$scan_date = array(
	'01' => __( '01', 'website-file-changes-monitor' ),
	'02' => __( '02', 'website-file-changes-monitor' ),
	'03' => __( '03', 'website-file-changes-monitor' ),
	'04' => __( '04', 'website-file-changes-monitor' ),
	'05' => __( '05', 'website-file-changes-monitor' ),
	'06' => __( '06', 'website-file-changes-monitor' ),
	'07' => __( '07', 'website-file-changes-monitor' ),
	'08' => __( '08', 'website-file-changes-monitor' ),
	'09' => __( '09', 'website-file-changes-monitor' ),
	'10' => __( '10', 'website-file-changes-monitor' ),
	'11' => __( '11', 'website-file-changes-monitor' ),
	'12' => __( '12', 'website-file-changes-monitor' ),
	'13' => __( '13', 'website-file-changes-monitor' ),
	'14' => __( '14', 'website-file-changes-monitor' ),
	'15' => __( '15', 'website-file-changes-monitor' ),
	'16' => __( '16', 'website-file-changes-monitor' ),
	'17' => __( '17', 'website-file-changes-monitor' ),
	'18' => __( '18', 'website-file-changes-monitor' ),
	'19' => __( '19', 'website-file-changes-monitor' ),
	'20' => __( '20', 'website-file-changes-monitor' ),
	'21' => __( '21', 'website-file-changes-monitor' ),
	'22' => __( '22', 'website-file-changes-monitor' ),
	'23' => __( '23', 'website-file-changes-monitor' ),
	'24' => __( '24', 'website-file-changes-monitor' ),
	'25' => __( '25', 'website-file-changes-monitor' ),
	'26' => __( '26', 'website-file-changes-monitor' ),
	'27' => __( '27', 'website-file-changes-monitor' ),
	'28' => __( '28', 'website-file-changes-monitor' ),
	'29' => __( '29', 'website-file-changes-monitor' ),
	'30' => __( '30', 'website-file-changes-monitor' ),
);

// WP Directories.
$wp_directories = wfcm_get_server_directories( 'display' );

$wp_directories = apply_filters( 'wfcm_file_changes_scan_directories', $wp_directories );

$disabled = 'no' === $settings['enabled'] ? 'disabled' : false;
?>

<div class="wrap wfcm-settings">
	<h1><?php esc_html_e( 'Website File Changes Settings', 'website-file-changes-monitor' ); ?></h1>
	<?php self::show_messages(); ?>
	<form method="post" action="" enctype="multipart/form-data">
		<h3><?php esc_html_e( 'Which file changes do you want to be notified of?', 'website-file-changes-monitor' ); ?></h3>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-file-changes-type"><?php esc_html_e( 'Notify me when files are', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label for="added">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="added" <?php echo in_array( 'added', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'added', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="deleted">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="deleted" <?php echo in_array( 'deleted', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'deleted', 'website-file-changes-monitor' ); ?></span>
						</label>
						<br>
						<label for="modified">
							<input type="checkbox" name="wfcm-settings[scan-type][]" value="modified" <?php echo in_array( 'modified', $settings['type'], true ) ? 'checked' : false; ?>>
							<span><?php esc_html_e( 'modified', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Type of Changes -->

		<h3><?php esc_html_e( 'When should the plugin scan your website for file changes?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin will run file changes scans once a week. If you can, ideally you should run file changes scans on a daily basis. The file changes scanner is very efficient and requires very little resources. Though if you have a fairly large website we recommend you to scan it when it is the least busy. The scan process should only take a few seconds to complete.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-frequency"><?php esc_html_e( 'Scan Frequency', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<select name="wfcm-settings[scan-frequency]">
							<?php foreach ( $frequency_options as $value => $html ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['frequency'] ); ?>><?php echo esc_html( $html ); ?></option>
							<?php endforeach; ?>
						</select>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th><label for="wfcm-settings-scan-hour"><?php esc_html_e( 'Scan Time', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<label>
							<select name="wfcm-settings[scan-hour]">
								<?php foreach ( $scan_hours as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['hour'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Hour', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-day]">
								<?php foreach ( $scan_days as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['day'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>

						<label>
							<select name="wfcm-settings[scan-date]">
								<?php foreach ( $scan_date as $value => $html ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $settings['date'] ); ?>><?php echo esc_html( $html ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<span class="description"><?php esc_html_e( 'Day', 'website-file-changes-monitor' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Scan frequency -->

		<h3><?php esc_html_e( 'Which directories should be scanned for file changes?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan all the directories in your WordPress website by default because that is the most secure option. Though if for some reason you do not want the plugin to scan any of these directories you can uncheck them from the below list.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th><label for="wfcm-settings-directories"><?php esc_html_e( 'Directories to scan', 'website-file-changes-monitor' ); ?></label></th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php foreach ( $wp_directories as $value => $html ) : ?>
								<label>
									<input name="wfcm-settings[scan-directories][]" type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php echo in_array( $value, $settings['directories'], true ) ? 'checked' : false; ?> />
									<?php echo esc_html( $html ); ?>
								</label>
								<br />
							<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- Scan directories -->

		<h3><?php esc_html_e( 'What is the biggest file size the plugin should scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'By default the plugin does not scan files that are bigger than 5MB. Such files are not common, hence typically not a target.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-file-size"><?php esc_html_e( 'File Size Limit', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<input type="number" name="wfcm-settings[scan-file-size]" min="1" max="100" value="<?php echo esc_attr( $settings['file-size'] ); ?>" /> <?php esc_html_e( 'MB', 'website-file-changes-monitor' ); ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Maximum File Size -->

		<h3><?php esc_html_e( 'Do you want to exclude specific files or files with a particular extension from the scan?', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'The plugin will scan everything that is in the WordPress root directory or below, even if the files and directories are not part of WordPress. It is recommended to scan all source code files and only exclude files that cannot be tampered, such as text files, media files etc, most of which are already excluded by default.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tr>
				<th><label for="wfcm-settings-exclude-dirs"><?php esc_html_e( 'Exclude All Files in These Directories', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-dirs-list">
								<?php foreach ( $settings['exclude-dirs'] as $dir ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-dirs][]" id="<?php echo esc_attr( $dir ); ?>" value="<?php echo esc_attr( $dir ); ?>" checked />
										<label for="<?php echo esc_attr( $dir ); ?>"><?php echo esc_html( $dir ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="dirs" type="button" value="<?php esc_html_e( 'REMOVE', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="dirs" type="button" value="<?php esc_html_e( 'ADD', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description">
							<?php esc_html_e( 'Specify the name of the directory and the path to it in relation to the website\'s root. For example, if you want to want to exclude all files in the sub directory dir1/dir2 specify the following:', 'website-file-changes-monitor' ); ?>
							<br>
							<?php echo esc_html( trailingslashit( ABSPATH ) ) . 'dir1/dir2/'; ?>
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude directories -->
			<tr>
				<th><label for="wfcm-settings-exclude-filenames"><?php esc_html_e( 'Exclude These Files', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-files-list">
								<?php foreach ( $settings['exclude-files'] as $file ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-files][]" id="<?php echo esc_attr( $file ); ?>" value="<?php echo esc_attr( $file ); ?>" checked />
										<label for="<?php echo esc_attr( $file ); ?>"><?php echo esc_html( $file ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="files" type="button" value="<?php esc_html_e( 'REMOVE', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="files" type="button" value="<?php esc_html_e( 'ADD', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the name and extension of the file(s) you want to exclude. Wildcard not supported. There is no need to specify the path of the file.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude filenames -->
			<tr>
				<th><label for="wfcm-settings-exclude-extensions"><?php esc_html_e( 'Exclude these File Types', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset <?php echo esc_attr( $disabled ); ?>>
						<div class="wfcm-files-container">
							<div class="exclude-list" id="wfcm-exclude-exts-list">
								<?php foreach ( $settings['exclude-exts'] as $file_type ) : ?>
									<span>
										<input type="checkbox" name="wfcm-settings[scan-exclude-exts][]" id="<?php echo esc_attr( $file_type ); ?>" value="<?php echo esc_attr( $file_type ); ?>" checked />
										<label for="<?php echo esc_attr( $file_type ); ?>"><?php echo esc_html( $file_type ); ?></label>
									</span>
								<?php endforeach; ?>
							</div>
							<input class="button remove" data-exclude-type="exts" type="button" value="<?php esc_html_e( 'REMOVE', 'website-file-changes-monitor' ); ?>" />
						</div>
						<div class="wfcm-files-container">
							<input class="name" type="text">
							<input class="button add" data-exclude-type="exts" type="button" value="<?php esc_html_e( 'ADD', 'website-file-changes-monitor' ); ?>" />
						</div>
						<p class="description"><?php esc_html_e( 'Specify the extension of the file types you want to exclude. You should exclude any type of logs and backup files that tend to be very big.', 'website-file-changes-monitor' ); ?></p>
					</fieldset>
				</td>
			</tr>
			<!-- Exclude extensions -->
		</table>
		<!-- Exclude directories, files, extensions -->

		<h3><?php esc_html_e( 'Launch an instant file changes scan', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Click the Scan Now button to launch an instant file changes scan using the configured settings. You can navigate away from this page during the scan. Note that the instant scan can be more resource intensive than scheduled scans.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table wfcm-table">
			<tbody>
				<tr>
					<th>
						<label><?php esc_html_e( 'Launch Instant Scan', 'website-file-changes-monitor' ); ?></label>
					</th>
					<td>
						<fieldset <?php echo esc_attr( $disabled ); ?>>
							<?php if ( 'yes' === $settings['enabled'] && ! wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan Now', 'website-file-changes-monitor' ); ?>">
								<input type="button" class="button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop Scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php elseif ( 'no' === $settings['enabled'] && wfcm_get_setting( 'scan-in-progress', false ) ) : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan in Progress', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-ui-primary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop Scan', 'website-file-changes-monitor' ); ?>">
								<!-- Scan in progress -->
							<?php else : ?>
								<input type="button" class="button button-primary" id="wfcm-scan-start" value="<?php esc_attr_e( 'Scan Now', 'website-file-changes-monitor' ); ?>" disabled>
								<input type="button" class="button button-secondary" id="wfcm-scan-stop" value="<?php esc_attr_e( 'Stop Scan', 'website-file-changes-monitor' ); ?>" disabled>
							<?php endif; ?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- / Instant Scan -->

		<h3><?php esc_html_e( 'Enable File Scanning', 'website-file-changes-monitor' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Use this switch to temporarily disable file scanning. When you disable and re-enable file scanning the plugin will report all the file changes it identifies when it compares the files between the last scan before it was scanning was disabled and the first scan when it was enabled.', 'website-file-changes-monitor' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="wfcm-file-changes"><?php esc_html_e( 'Keep a Log of File Changes', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<label><input name="wfcm-settings[keep-log]" type="radio" value="yes" <?php checked( $settings['enabled'], 'yes' ); ?>><?php esc_html_e( 'Yes', 'website-file-changes-monitor' ); ?></label>
						<br>
						<label><input name="wfcm-settings[keep-log]" type="radio" value="no" <?php checked( $settings['enabled'], 'no' ); ?>><?php esc_html_e( 'No', 'website-file-changes-monitor' ); ?></label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Disable File Changes -->

		<table class="form-table wfcm-settings-danger">
			<tr>
				<th><label for="wfcm-file-changes"><?php esc_html_e( 'Delete plugin data upon uninstall', 'website-file-changes-monitor' ); ?></label></th>
				<td>
					<fieldset>
						<label><input name="wfcm-settings[delete-data]" type="checkbox" value="1" <?php checked( $settings['delete-data'] ); ?>></label>
					</fieldset>
				</td>
			</tr>
		</table>
		<!-- Delete plugin data and settings -->

		<?php
		wp_nonce_field( 'wfcm-save-admin-settings' );
		submit_button();
		?>
	</form>
</div>
