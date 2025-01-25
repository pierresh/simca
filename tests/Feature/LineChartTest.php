<?php

use Pierresh\Simca\Charts\LineChart;
use Pierresh\Simca\Model\Dot;

test('create a new chart', function () {
	$dotsSeries = (new LineChart(600, 400))
		->setSeries([[10, 45, 30, 25]])
		->setLabels(['A', 'B', 'C', 'D'])
		->addObjectiveY1(20, 'red', 1)
		->getDots();

	$dotsSerie = $dotsSeries[0];

	expect(count($dotsSeries))->toEqual(1);
	expect(count($dotsSerie))->toEqual(4);
	expect($dotsSerie[0])->toEqual(new Dot(120, 308, 10));
	expect($dotsSerie[1])->toEqual(new Dot(253.33, 56, 45));
	expect($dotsSerie[2])->toEqual(new Dot(386.67, 164, 30));
	expect($dotsSerie[3])->toEqual(new Dot(520, 200, 25));
});
