<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Search Results</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
	</head>
	<body>
		<div id="lucos_navbar">
			<a href="https://l42.eu/"><img src="https://l42.eu/logo.png" alt="lucOS" id="lucos_navbar_icon" /></a>
			<span id="lucos_navbar_title">Metadata Manager - Search Results</span>
		</div>
		<div id="content">
<h2>Tracks</h2>
<ul>
<?php foreach ($tracks as $track) {
	?><li>
		<h3><?=htmlspecialchars($track["title"])?></h3>
		<span class="queueactions">
			<form action="/tracks/<?=$track["id"]?>" method="get">
				<input type="submit" value="Edit" />
			</form>
			<?php foreach (array("now" => "Play Now", "next" => "Play Next", "end" => "Queue Track") as $pos => $label) {?> 
			<form action="/tracks/queue" method="post">
				<input type="hidden" name="url" value="<?=htmlspecialchars($track["url"])?>" />
				<input type="hidden" name="pos" value="<?=$pos?>" />
				<input type="submit" value="<?=$label?>" />
			</form>
			<?php } ?>
		</span>
	</li><?php
}
?>
</ul>
<div class="pagination">
<?php
$nextpage = $page + 1;
$prevpage = $page - 1;
if ($prevpage > 0) {
	?><a href="<?=htmlspecialchars("/search?q=".urlencode($query)."&page=$prevpage")?>">&lt;- Prev</a> | <?php
}
?>
Page <?=$page?> |
	<a href="<?=htmlspecialchars("/search?q=".urlencode($query)."&page=$nextpage")?>">Next -&gt;</a>
</div>
</div>
</body>
</html>