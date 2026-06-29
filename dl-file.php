<?php
/*
 * dl-file.php
 *
 * Protect uploaded files with login.
 *
 * Based upon:
 * http://wordpress.stackexchange.com/questions/37144/protect-wordpress-uploads-if-user-is-not-logged-in
 *
 * Heavily modified to enable range requests and remove issues with large files.
 *
 *
 * Original license:
 * @author hakre <http://hakre.wordpress.com/>
 * @license GPL-3.0+
 * @registry SPDX
 */

require_once 'wp-load.php';

list($basedir) = array_values(array_intersect_key(wp_upload_dir(), array('basedir' => 1))) + array(null);

$fileurl = get_home_url() . '/wp-content/uploads/' . $_GET['file'];

$file = rtrim($basedir, '/') . '/' . str_replace('..', '', isset($_GET['file']) ? $_GET['file'] : '');
if (!$basedir || !is_file($file)) {
    status_header(404);
    die('File not found.');
}

$mime = wp_check_filetype($file);
if (false === $mime['type'] && function_exists('mime_content_type')) {
    $mime['type'] = mime_content_type($file);
}

if ($mime['type']) {
    $mimetype = $mime['type'];
} else {
    $fileextension = substr($file, strrpos($file, '.') + 1);
    if ($fileextension === 'svg') {
        $mimetype = 'image/svg+xml';
    } else {
        $mimetype = 'image/' . $fileextension;
    }
}

$is_public = false;
$attachment_id = vizoo_get_attachment_from_url($fileurl);

if (!empty($attachment_id)) {
    if (get_field('publicly_accessible', $attachment_id) == '1') {
        $is_public = true;
    }
}

if (is_user_logged_in() || strstartswith($mimetype, 'image') || $is_public) {
    $filesize = filesize($file);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mimetype); // always send this

    // Enable range requests
    // Based upon the article: https://licson.net/post/stream-videos-php/
    if (isset($_SERVER['HTTP_RANGE'])) {
        $ranges = array_map('intval', explode('-', substr($_SERVER['HTTP_RANGE'], 6)));
        if (!$ranges[1]) {
            $ranges[1] = $filesize - 1;
        }
        status_header(206);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($ranges[1] - $ranges[0] + 1));
        header(sprintf('Content-Range: bytes %d-%d/%d', $ranges[0], $ranges[1], $filesize));

        // Modified as in https://github.com/ignacionelson/ProjectSend/issues/173 because of issues with large files.
        // Stream the file contents in chunks
        $context = stream_context_create();
        $f = fopen($file, 'rb', false, $context);
        $chunk_size = 8192;
        fseek($f, $ranges[0]);

        // Turn off output buffering, to prevent memory issues with large files (see: https://stackoverflow.com/a/31277949)
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; $i++) {
            ob_end_clean();
        }
        session_write_close();
        flush();

        while (!feof($f) && ftell($f) < $ranges[1]) {
            echo stream_get_contents($f, $chunk_size);

            flush();
        }

        fclose($f);
    } else {
        // Send headers
        if (!strstartswith($mimetype, 'video') && !strstartswith($mimetype, 'image') && !strstartswith($mimetype, 'audio') && $mimetype != 'application/pdf') {
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        }

        if (false === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS')) {
            header('Content-Length: ' . $filesize);
        }
        $last_modified = gmdate('D, d M Y H:i:s', filemtime($file));
        header('Last-Modified: ' . $last_modified . ' GMT');
        header('Cache-Control: private', false);
        header('Connection: close');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 100000000) . ' GMT');

        // Modified as in https://github.com/ignacionelson/ProjectSend/issues/173 because of issues with large files.
        // Stream the file contents in chunks
        $context = stream_context_create();
        $f = fopen($file, 'rb', false, $context);
        $chunk_size = 8192;

        // Turn off output buffering, to prevent memory issues with large files (see: https://stackoverflow.com/a/31277949)
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; $i++) {
            ob_end_clean();
        }
        session_write_close();
        flush();

        while (!feof($f)) {
            echo stream_get_contents($f, $chunk_size);

            flush();
        }

        fclose($f);
    }
    $previous_download_count = (int)get_post_meta($attachment_id, 'vizoo_media_downloads', true);
    update_post_meta($attachment_id, 'vizoo_media_downloads', $previous_download_count + 1);
    exit;
} else {
    auth_redirect();
}

function vizoo_get_attachment_from_url($url)
{
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SELECT ID FROM ' . $wpdb->posts . ' WHERE guid = "%s"', $url));
}

function strstartswith($string, $needle)
{
    return substr($string, 0, strlen($needle)) == $needle;
}
