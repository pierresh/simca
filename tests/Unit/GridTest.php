<?php

use Pierresh\Simca\Charts\Grid;

test('functions related to grid', function () {
	$lines = (new Grid())->autoGridLines(0, 45, 5);

	expect($lines[0])->toEqual(0);
	expect($lines[1])->toEqual(12.5);
	expect($lines[2])->toEqual(25);
	expect($lines[3])->toEqual(37.5);
	expect($lines[4])->toEqual(50);
});
