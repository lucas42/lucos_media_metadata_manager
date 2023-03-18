<!DOCTYPE html>
<html>
	<head>
		<title>Lucos Media Metadata Manager - Search Results</title>
		<link href="/style.css" rel="stylesheet">
		<link rel="icon" href="/icon" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="mobile-web-app-capable" content="yes">
		<script src="/track-ui.js"></script>
		<script src="/queue-controls.js"></script>
	</head>
	<body>
		<lucos-navbar bg-colour="#000020"><a href="/" id="lucos_navbar_title">Metadata Manager</a></lucos-navbar>
		<div class="content_container">
			<div id="content">
<h2>Tracks</h2>
<ul>
<?php foreach ($tracks as $track) {
	?><li>
		<h3><?=htmlspecialchars($track["title"])?></h3>
		<queue-controls
			data-trackurl="<?=htmlspecialchars($track["url"])?>"
			data-trackid="<?=$track["id"]?>" />
	</li><?php
}
?>
</ul>
<div class="pagination">
<?php
$nextpage = $page + 1;
$prevpage = $page - 1;
if ($prevpage > 0) {
	?><a href="<?=htmlspecialchars("/search?${basequerystring}&page=${prevpage}")?>">&lt;- Prev</a> | <?php
}
?>
Page <?=$page?> out of <?=$totalPages?>
<?php if ($page < $totalPages) {
?>
	| <a href="<?=htmlspecialchars("/search?${basequerystring}&page=${nextpage}")?>">Next -&gt;</a>
<?php
}
?>
</div>
			</div>
			<div id="bulk-edit">

				<h2 id="advanced">Bulk Edit</h2>
				<small>Updates all non-blank fields for the tracks returned by the current search</small>
				<form method="post" id="trackform">
				<?php foreach ($form_fields as $key => $field) {
					$value = null;
				?>
					<div class="form-field">
					<?php
						include 'field.php';
					?>
					</div>
				<?php } ?>
					<input type="submit" value="Edit All" id="save" class="primary-submit" />
				</form>
			</div>
		</div>
		<script src="/lucos_navbar.js" type="text/javascript"></script>
	</body>
</html>