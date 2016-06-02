<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Chart Builder</title>
		<style media="screen">
			span.command,
			pre {
				color: #fff;
				background: #000;
				font-family: Menlo, monospace;
				padding: 10px;
				font-size: 12px;
			}
			span.command {
				font-weight: bold;
				color: yellow;
				display: inline-block;
			}
			a {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 2em;
			}
		</style>
	</head>
	<body>

<?php

// Get our contentftp username & password and google docs auth.
$json = json_decode(file_get_contents('/home/nd/.abc-credentials'));

$env = array(
	"GOOGLE_OAUTH_CLIENT_ID" => $json->dailgraphics->GOOGLE_OAUTH_CLIENT_ID,
	"GOOGLE_OAUTH_CONSUMER_SECRET" => $json->dailgraphics->GOOGLE_OAUTH_CONSUMER_SECRET,
	"AUTHOMATIC_SALT" => $json->dailgraphics->AUTHOMATIC_SALT,
	"FTP_PASS" => $json->contentftp->password,
	"FTP_USER" => $json->contentftp->username
);

foreach ($env as $key=>$val) {
	putenv("{$key}={$val}");
}

function clean($string) {
	$string = strtolower($string); // Convert to lowercase.
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	return preg_replace('/[^a-z0-9\-]/', '', $string); // Removes special chars.
}

$slug = $_POST['slug'];
$type = "graphic";
if (isset($_POST['type'])) {
	$type = $_POST['type'];
}

if ($slug) {
	chdir("dailygraphics");
	switch ($_GET['action']) {
		case "create":
			$slug = clean($slug);
			$command = "fab add_{$type}:{$slug}";
			echo "<span class='command'>{$command}</span>";
			echo "<pre>";
			$x = system($command);
			echo "</pre>";
			if (strpos($x, "Visit newsdev3:8888/oauth") !== false) {
				header("Location: http://newsdev3.aus.aunty.abc.net.au:8888/authenticate");
				exit;
			}
			// auto deploy when first created
			$command = "fab deploy:{$slug}";
			echo "<span class='command'>{$command}</span>";
			echo "<pre>";
			system($command);
			echo "</pre>";
			break;

		case "deploy":
			$command = "fab deploy:{$slug}";
			echo "<span class='command'>{$command}</span>";
			echo "<pre>";
			system($command);
			echo "</pre>";
			break;

		case "deploy_template":
			$command = "fab deploy_template:{$slug},template={$type}";
			echo "<span class='command'>{$command}</span>";
			echo "<pre>";
			system($command);
			echo "</pre>";
			break;

		case "remove":
			$command = "rm -rf ../graphics/{$slug}";
			echo "<span class='command'>{$command}</span>";
			echo "<pre>";
			system($command);
			echo "</pre>";
			// TODO: also remove from PROD?
			break;
	}
}

$redirect = ".";
if (isset($_POST['redirect'])) {
	$redirect = "http://www.abc.net.au/dat/news/interactives/graphics/" . $slug . "/";
}

//header("Location: " . $redirect);
echo "<div><a href='{$redirect}'>Continue</a></div>";
?>

</body>
</html>