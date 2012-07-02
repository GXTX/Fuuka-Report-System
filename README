Maybe I will rewrite this readme but for now this can suffice.

Requires: reCAPTCHA lib

SQL:
```
CREATE TABLE IF NOT EXISTS `user_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` text NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `category` enum('illegal','spam') NOT NULL,
  `report_time` int(11) NOT NULL,
  `ipv4` varchar(15) NOT NULL,
  `action` enum('deleted','none','new') NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `report_time` (`report_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
```

JS:
```
<script type="text/javascript">
        function report(url){
                var width  = 750;
                var height = 250;
                var left   = (screen.width  - width)/2;
                var top    = (screen.height - height)/2;
                var params = 'width='+width+', height='+height+', top='+top+', left='+left+', directories=no, location=no, menubar=no, resizable=no, scrollbars=no, status=yes, toolbar=no';
                newwin=window.open(url,'Report', params);
                if (window.focus) {
                        newwin.focus()
                }
                return false;
        }
</script>
```

Add in POSTS_INCLUDE_POST_HEADER:
```
[<a href="javascript: void(0);" onclick="report('https://archive.installgentoo.net/admin/user_report.php?postid=<var ref_post_text($num,$subnum)>&board=<var $board_name>')">Report</a>]
```
