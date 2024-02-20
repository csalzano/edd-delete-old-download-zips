<?php
/**
 * Plugin Name: Easy Digital Downloads - Delete Old Download Zips
 * Plugin URI: https://github.com/csalzano/edd-delete-old-download-zips
 * Description: Deletes .zip files from Media Library that are no longer associated with downloads. Active and deactivate.
 * Version: 1.0.0
 * Author: Corey Salzano
 * Author URI: https://breakfastco.xyz
 * Text Domain: edd-delete-old-download-zips
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package edd-delete-old-download-zips
 * @author Corey Salzano <csalzano@duck.com>
 */

defined( 'ABSPATH' ) || exit;

register_activation_hook( __FILE__, 'breakfast_edd_delete_old_download_zips' );
/**
 * Activation hook callback. Deletes .zip files from the Media Library that are
 * no longer associated with an Easy Digital Downloads download.
 *
 * @return void
 */
function breakfast_edd_delete_old_download_zips() {
	$downloads = get_posts(
		array(
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'post_type'      => 'download',
			'posts_per_page' => 100,
		)
	);
	foreach ( $downloads as $download ) {
		// Which attachments are currently linked to the download? Keep these.
		$files    = get_post_meta( $download->ID, 'edd_download_files', true );
		$keep_ids = array();
		foreach ( $files as $file ) {
			$keep_ids[] = $file['attachment_id'];
		}
		// Are there other .zip attachments with this download as a parent?
		$other_zips = get_posts(
			array(
				'exclude'        => $keep_ids,
				'post_mime_type' => 'application/zip',
				'post_parent'    => $download->ID,
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'posts_per_page' => 100,
			)
		);
		foreach ( $other_zips as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}
	}
}
