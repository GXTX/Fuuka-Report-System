<?php
if (function_exists('smarty_block_t')===false)
	$this->getLoader()->loadPlugin('t');
ob_start(); /* template body */ ?><!DOCTYPE html>
<html>
<head>
<title>Error</title>
<link rel="stylesheet" type="text/css" href="<?php echo KU_ARCHIVEPATH;?>media/fuuka.css" title="Fuuka" />
<style type="text/css"><!-- html,body { background:#eefff2; color:#002200; } img { border: none; } a { color:#34345c; } a:visited { color:#34345c; } a:hover { color:#DD0000; } .js, .js a { color:black;text-decoration:none; } .js:hover, .js a:hover { color:black;font-weight:bold;text-decoration:underline; } .thumb, .nothumb { float: left; margin: 2px 20px; } .doubledash { vertical-align:top;clear:both;float:left; } .inline { vertical-align:top; } .reply { background:#d6f0da; } .subreply { background:#cce1cf; } .highlight { background:#d6bad0; } .unkfunc{ color:#789922; } .postername { color:#117743; font-weight:bold; text-decoration: none; } .postertrip { color:#228854; text-decoration: none; } a.tooltip span, a.tooltip-red span { display:none; } --></style>

<style type="text/css">
body {
	width: 100% !important;
}
</style>
</head>
<body>
<h1 style="font-size: 3em;"><?php  if (!isset($_tag_stack)){ $_tag_stack = array(); } $_tag_stack[] = array(); $_block_repeat=true; smarty_block_t($_tag_stack[count($_tag_stack)-1], null, $this, $_block_repeat); while ($_block_repeat) { ob_start();?>Error<?php  $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_t($_tag_stack[count($_tag_stack)-1], $_block_content, $this, $_block_repeat); } array_pop($_tag_stack);?></h1>
<br />
<h2 style="font-size: 2em;font-weight: bold;text-align: center;">
<?php echo $this->scope["errormsg"];?>

</h2>
<?php echo $this->scope["errormsgext"];?>

<div style="text-align: center;width: 100%;position: absolute;bottom: 10px;">
<br />
</div>
</body>
</html><?php  /* end template body */
return $this->buffer . ob_get_clean();
?>