<?php

use Pierresh\Simca\Charts\LineChart;
use Pierresh\Simca\Charts\BarChart;
use Pierresh\Simca\Charts\BubbleChart;
use Pierresh\Simca\Charts\PieChart;
use Pierresh\Simca\Charts\RadarChart;

$chart = (new BarChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([[10, 45, 30, 25], [15, 20, 15, 25], [5, 2, 10, 15]])
	->setLabels(['A', 'B', 'C', 'D'])
	->setColors(['#2dd55b', '#ffc409', '#0054e9'])
	->setOptions([
		'stacked' => true,
		'nbYkeys2' => 1,
	])
	->render();

echo $chart;

$chart = (new LineChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([[10, 45, 30, 25], [15, 20, 15, 25]])
	->setLabels([
		'2024-06-01 08:00',
		'2024-06-01 09:00',
		'2024-06-01 13:00',
		'2024-06-01 13:30',
	])
	->addObjectiveY1(20)
	->addEvent('2024-06-01 10:00')
	->addEvent('2024-06-01 12:15')
	->showTrend()
	->setOptions([
		'timeChart' => true,
		'unitY1' => 'T',
		'nbYkeys2' => 1,
		'margin' => 60,
	])
	->render();

echo $chart;

$chart = (new PieChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([[14, 0.5], [3, 0.9], [5, 0.8], [5, 1], [5, 0.9]])
	->render();

echo $chart;

$chart = (new BubbleChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([
		['2024-06-01 08:00', 40, 30],
		['2024-06-01 09:00', 20, 15],
		['2024-06-01 12:00', 30, 25],
	])
	->setOptions([
		'timeChart' => true,
	])
	->render();

echo $chart;

$chart = (new LineChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([[10, 45, 30, 25], [15, 20, 15, 25]])
	->setLabels([
		'2024-06-01 08:00',
		'2024-06-01 09:00',
		'2024-06-01 13:00',
		'2024-06-01 13:30',
	])
	->setOptions([
		'timeChart' => true,
		'unitY1' => 'T',
		'margin' => 0,
		'stacked' => true,
		'fillOpacity' => 0.3,
	])
	->render();

echo $chart;

$chart = (new RadarChart(600, 400))
	->setOptions(['responsive' => false])
	->setSeries([
		[65, 59, 90, 81, 56, 55, 40],
		[38, 48, 40, 19, 96, 27, 100]
	])
	->setLabels([
		'Eating',
		'Drinking',
		'Sleeping',
		'Designing',
		'Coding',
		'Cycling',
		'Running',
	])
	->setOptions([
		'fillOpacity' => 0.3,
	])
	->render();

echo $chart;

