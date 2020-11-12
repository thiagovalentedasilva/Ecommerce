<?php
use \thiagova\Page;
$app->get('/', function() {
	$page = new Page();
	$page->setTpl("index");
});