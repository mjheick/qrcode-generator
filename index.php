<?php
require_once('./phpqrcode/phpqrcode.php');
/**
 * Take in whatever is posted, return back whatever we need
 */
if (isset($_POST['qr']))
{
	$qrtext = $_POST['qr']; /* not trimming because spaces might be necessary */
	if (strlen($qrtext) == 0) {
		echo '{"error": "no data"}';
		die();
	}
	$qr_data = QRcode::text($qrtext);
	$svg_dimensions = count($qr_data);
	$svg_data = [];
	$svg_data[] = '<svg viewBox="0 0 ' . $svg_dimensions . ' ' . $svg_dimensions . '" xmlns="http://www.w3.org/2000/svg">';
	for ($y = 0; $y < $svg_dimensions; $y++)
	{
		$horizon = $qr_data[$y];
		for ($x = 0; $x < strlen($horizon); $x++)
		{
			if (substr($horizon, $x, 1) == 1)
			{
				$svg_data[] = ' <rect width="1" height="1" x="' . $x . '" y="' . $y . '" style="fill:black;stroke:black;stroke-width:0;" />';
			}
		}
	}
	$svg_data[] = '</svg>';
	$svg = implode("\n", $svg_data);
	$qrdata = base64_encode($svg);
	$return = ['image' => 'data:image/svg+xml;base64,' . $qrdata, 'text' => $qrtext];
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
				let dim = document.getElementById('qr-dimensions');
				let qr = document.getElementById('qr-image');
				qr.src = j.image;
				let px = parseInt(dim.value);
				if (px < 30) { px = 30; }
				qr.style.width = px + 'px';
				qr.style.height = px + 'px';
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
			<textarea id="make-into-qr">Enter stuff here</textarea>
		</div>
		<div>
			Image px:<input id="qr-dimensions" type="text" value="50" placeholder="50" />
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
window.addEventListener("load", (event) => {
	let resize = document.getElementById('qr-dimensions');
	resize.addEventListener('keyup', generateQR);
});
	</script>
</html>