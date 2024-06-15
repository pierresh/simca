<?php

use Pierresh\Simca\Charts\LineChart;

$chart = (new LineChart(600, 400))
	->setSeries([[10, 45, 30, 25]])
	->setLabels(['A', 'B', 'C', 'D'])
	->addObjectiveY1(20, 'red', 1)
	->render();

echo $chart;
