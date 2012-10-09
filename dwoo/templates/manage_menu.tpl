<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{t}Manage Boards{/t}</title>
	<link rel="stylesheet" type="text/css" href="{%KU_WEBPATH}/css/site_kusabax.css" />
	<link rel="stylesheet" type="text/css" href="{%KU_WEBPATH}/css/sitemenu_kusabax.css" />
<link rel="shortcut icon" href="{%KU_WEBPATH}/favicon.ico" />
{literal}
<script type="text/javascript">
function toggle(button, area) {
	var tog=document.getElementById(area);
	if(tog.style.display)	{
		tog.style.display="";
	} else {
		tog.style.display="none";
	}
	button.innerHTML=(tog.style.display)?'+':'&minus;';
	createCookie('nav_show_'+area, tog.style.display?'0':'1', 365);
}
</script>
{/literal}
<base target="manage_main" />
</head>
<body>
<h1>{t}Manage Boards{/t}</h1>
<ul>
	{$links}
</ul>
</body>
</html>