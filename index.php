<?php

define( 'ROOTPATH', __DIR__ );
require_once 'config.php';
require_once 'vendor/autoload.php';

$client = new \Github\Client();
$client->authenticate( GITHUB_TOKEN, null, \Github\Client::AUTH_HTTP_TOKEN );

$plugin = isset($_GET['plugin']) ? $_GET['plugin'] : '';
if ( ! in_array( $plugin, PLUGINS ) ) {
	die( 'Invalid plugin' );
}

$pluginZip = ROOTPATH . '/plugins/' . $plugin . '.zip';

ob_start();
echo '<pre>Please wait while we generate your demo site...' . "\n";
ob_flush();
flush();

if ( ! file_exists( $pluginZip ) ) {
	$release = $client->api( 'repo' )->releases()->latest( GITHUB_USER, $plugin );
	$assets  = $client->api( 'repo' )->releases()->assets()->all( GITHUB_USER, $plugin, $release['id'] );

	if ( isset( $assets[0]['url'] ) ) {
		if ( DEBUG ) {
			echo 'Downloading ' . $assets[0]['url'] . '...' . "\n";
		}

		$guzzle = new \GuzzleHttp\Client();
		$guzzle->request( 'GET', $assets[0]['url'], [
			'headers' => [
				'Authorization' => 'token ' . GITHUB_TOKEN,
				'Accept'        => 'application/octet-stream',
			],
			'sink'    => $pluginZip,
		] );
	}
}

$hash    = md5( $plugin . time() );
$demoUrl = DEMO_URL . '/site/' . $hash;
exec( ROOTPATH . '/create-demo-site.sh ' . DB_USER . ' ' . DB_PASS . ' ' . $hash . ' ' . $pluginZip . ' ' . $demoUrl . ' /dev/null 2>&1', $output, $exitCode );
if ( DEBUG ) {
	echo implode( "\n", $output );
}

echo "\n";
if ( $exitCode ) {
	echo implode( "\n", $output );
	echo 'Looks like there was an error. Please <a href="mailto:' . SUPPORT_EMAIL . '">contact support</a>' . "\n";
} else {
	echo 'Redirecting to demo site: <a href="' . $demoUrl . '">' . $demoUrl . '</a>' . "\n";
	echo '<script>window.location = "' . $demoUrl . '";</script>';
}
echo '</pre>';

ob_end_flush();
