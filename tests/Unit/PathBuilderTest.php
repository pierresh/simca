<?php

use Pierresh\Simca\Charts\Helper\PathBuilder;

test('creates simple path with moveTo and lineTo', function () {
	$path = PathBuilder::create()
		->moveTo(10, 20)
		->lineTo(30, 40)
		->build();

	expect($path)->toBe('M10,20L30,40');
});

test('creates path with horizontal and vertical lines', function () {
	$path = PathBuilder::create()
		->moveTo(0, 0)
		->horizontalTo(50)
		->verticalTo(30)
		->build();

	expect($path)->toBe('M0,0H50V30');
});

test('creates closed path', function () {
	$path = PathBuilder::create()
		->moveTo(10, 10)
		->lineTo(20, 10)
		->lineTo(15, 20)
		->closePath()
		->build();

	expect($path)->toBe('M10,10L20,10L15,20Z');
});

test('creates path with curves', function () {
	$path = PathBuilder::create()
		->moveTo(0, 0)
		->curveTo(10, 20, 30, 40, 50, 60)
		->build();

	expect($path)->toBe('M0,0C10,20 30,40 50,60');
});

test('creates path with quadratic curve', function () {
	$path = PathBuilder::create()
		->moveTo(0, 0)
		->quadraticTo(25, 50, 50, 0)
		->build();

	expect($path)->toBe('M0,0Q25,50 50,0');
});

test('can chain multiple path operations', function () {
	$path = PathBuilder::create()
		->moveTo(10, 10)
		->horizontalTo(50)
		->verticalTo(30)
		->lineTo(10, 30)
		->closePath()
		->build();

	expect($path)->toBe('M10,10H50V30L10,30Z');
});

test('handles floating point coordinates', function () {
	$path = PathBuilder::create()
		->moveTo(10.5, 20.75)
		->lineTo(30.25, 40.5)
		->build();

	expect($path)->toBe('M10.5,20.75L30.25,40.5');
});