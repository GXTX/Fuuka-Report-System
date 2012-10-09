<!DOCTYPE html>
<html>
<head>
<title>{t}Manage Boards{/t}</title>
	<link rel="stylesheet" type="text/css" href="{%KU_WEBPATH}/css/site_kusabax.css" />
{literal}<style type="text/css">
body, div, td, th, h2, h3, h4 { /* redundant rules for bad browsers */ 
	font-family: verdana,sans-serif;
	font-size:	x-small;
	voice-family: "\"}\"";
	voice-family: inherit;
	font-size: small;
} 
h1,h2 {
	font-family: trebuchet ms;
	font-weight: bold;
	color: #333;
}

h1 {
	font-size: 180%;
	margin: 0;
}

h2 {
	font-size: 140%;
	padding-bottom: 2px;
	border-bottom: 1px solid #CCC;
	margin: 0;
}
br {
	clear: left;
}

label,input {
	display: block;
	width: auto;
	float: left;
	margin-bottom: 10px;
}

label {
	font-size: 12px;
	text-align: right;
	width: 175px;
	padding-right: 20px;
}

.desc {
	text-indent: 5px;
	font-size : 80%;
	/*white-space: nowrap;*/
}
</style>{/literal}
<link rel="shortcut icon" href="{%KU_WEBPATH}/favicon.ico" />
</head>
<body style="min-width: 600px; padding: 1em 20px 3em 20px;">
{$includeheader}
<div id="main">
	<div id="contents">
		{$page}
	</div>
</div>	
{$footer}
</body>
</html>