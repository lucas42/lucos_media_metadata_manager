<?php

function getFormFields() {
	return [
		"title" => [
			"type" => "text",
		],
		"artist" => [
			"type" => "text",
		],
		"album" => [
			"type" => "text",
		],
		"rating" => [
			"type" => "range",
		],
		"event" => [
			"type" => "select",
			"values" => ["xmas","hallowe'en","eurovision"]
		],
		"format" => [
			"type" => "select",
			"values" => ["speech","fx","podcast"]
		],
	];
}

function getFormKeys() {
	return array_keys(getFormFields());
}