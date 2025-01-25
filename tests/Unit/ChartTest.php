<?php

use Pierresh\Simca\Charts\LineChart;

test('create a new chart', function () {
	$chart = (new LineChart(600, 400))
		->setSeries([[10, 45, 30, 25]])
		->setLabels(['A', 'B', 'C', 'D'])
		->render();

	expect($chart)->toBeString();
});
