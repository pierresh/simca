<?php

use Pierresh\Simca\Charts\BarChart;
use Pierresh\Simca\Model\Dot;

test('create a new simple bar chart', function () {
    $dotsSeries = (new BarChart(600, 400))
        ->setSeries([[10, 45, 30, 25], [15, 20, 15, 25]])
        ->setLabels(['A', 'B', 'C', 'D'])
        ->getDots();

    $dotsSerie = $dotsSeries[0];

    expect(count($dotsSeries))->toEqual(2);
    expect(count($dotsSerie))->toEqual(4);
    expect($dotsSerie[0])->toEqual(new Dot(125, 308, 10));
    expect($dotsSerie[1])->toEqual(new Dot(255, 56, 45));
    expect($dotsSerie[2])->toEqual(new Dot(385, 164, 30));
    expect($dotsSerie[3])->toEqual(new Dot(515, 200, 25));
});

test('create a new stacked bar chart with 2 axis', function () {
    $dotsSeries = (new BarChart(600, 400))
        ->setSeries([[10, 45, 30, 25], [15, 20, 15, 25], [15, 20, 15, 25]])
        ->setLabels(['A', 'B', 'C', 'D'])
        ->setOptions([
            'stacked' => true,
            'nbYkeys2' => 1,
        ])
        ->getDots();

    $dotsSerie = $dotsSeries[2];

    expect(count($dotsSeries))->toEqual(3);
    expect(count($dotsSerie))->toEqual(4);
    expect($dotsSerie[0])->toEqual(new Dot(120, 200, 15));
    expect($dotsSerie[1])->toEqual(new Dot(240, 140, 20));
    expect($dotsSerie[2])->toEqual(new Dot(360, 200, 15));
    expect($dotsSerie[3])->toEqual(new Dot(480, 80, 25));
});
