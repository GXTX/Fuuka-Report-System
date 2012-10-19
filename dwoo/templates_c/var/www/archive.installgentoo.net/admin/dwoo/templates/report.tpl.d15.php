<?php
ob_start(); /* template body */ ?><!DOCTYPE html>
<html>
	<head>
		<title>Report Post #<?php echo $this->scope["post"];?></title>
		<meta charset="UTF-8" /> 
		<link rel="stylesheet" type="text/css" href="<?php echo KU_ARCHIVEPATH;?>media/fuuka.css" title="Fuuka" />
		
		<style type="text/css"><!-- html,body { background:#eefff2; color:#002200; } img { border: none; } a { color:#34345c; } a:visited { color:#34345c; } a:hover { color:#DD0000; } .js, .js a { color:black;text-decoration:none; } .js:hover, .js a:hover { color:black;font-weight:bold;text-decoration:underline; } .thumb, .nothumb { float: left; margin: 2px 20px; } .doubledash { vertical-align:top;clear:both;float:left; } .inline { vertical-align:top; } .reply { background:#d6f0da; } .subreply { background:#cce1cf; } .highlight { background:#d6bad0; } .unkfunc{ color:#789922; } .postername { color:#117743; font-weight:bold; text-decoration: none; } .postertrip { color:#228854; text-decoration: none; } a.tooltip span, a.tooltip-red span { display:none; } --></style>
		<style>
			fieldset { margin-right: 25px; }
			.recaptchatable {background-color: transparent !important; border: none !important;}
			.recaptcha_image_cell {background-color: transparent !important;}
			#recaptcha_response_field {border: 1px solid #AAA !important;}
		</style>
		
	</head>
<body>
	<?php echo $this->scope["body"];?>

</body>
</html><?php  /* end template body */
return $this->buffer . ob_get_clean();
?>