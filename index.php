<?php
require_once('./phpqrcode/phpqrcode.php');
/**
 * Take in whatever is posted, return back whatever we need
 */
if (isset($_POST['qr']))
{
	$qrtext = $_POST['qr'];
	$qrtemp = tempnam(sys_get_temp_dir(), 'qr-');
	if (substr($qrtemp, -4) !== '.png')
	{
		if (file_exists($qrtemp))
		{
			unlink($qrtemp);
		}
		$qrtemp .= '.png';
	}
	QRcode::png($qrtext, $qrtemp);
	$qrdata = base64_encode(file_get_contents($qrtemp));
	unlink($qrtemp);
	$return = ['image' => 'data:image/png;base64,' . $qrdata, 'text' => $qrtext];
	$json = json_encode($return);
	if ($json === false)
	{
		echo '{"error": "problem in encoding data"}';
	}
	else
	{
		echo $json;
	}
	die();
}
?><!doctype html>
<html>
	<head>
		<style>
textarea {
	width: 100%;
}
		</style>
		<script>
function generateQR() {
	let v = document.getElementById('make-into-qr').value;
	let x = new XMLHttpRequest();
	x.onreadystatechange = () => {
		if (x.readyState === XMLHttpRequest.DONE && x.status === 200) {
			let d = x.responseText;
			let j = JSON.parse(d);
			if ("error" in j)
			{
				alert(j.error);
			}
			if ("image" in j)
			{
				document.getElementById('qr-image').src = j.image;
			}
		}
	};
	x.open('POST', 'index.php', true);
	x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	x.send('qr=' + encodeURIComponent(v));
}
		</script>
	</head>
	<body>
		<div>
			Enter Data to turn to QR code:
		</div>
		<div>
			<textarea id="make-into-qr">enter text here</textarea>
		</div>
		<div>
			<img id="qr-image" src="" title="qr code" />
		</div>
	</body>
	<script>
window.addEventListener("load", (event) => {
	let qr = document.getElementById('make-into-qr');
	qr.addEventListener('keyup', generateQR);
	qr.focus();
});
	</script>
</html>