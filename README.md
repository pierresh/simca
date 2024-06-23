# Simca

SVG charts for PHP

This project aims to be a complete solution for creating SVG charts in PHP.

This is useful when charts need to be genenerated in back-end to be included in emails and PDFs.

The project is currently in progress, but the line chart feature is already available.

## Installation
```php
composer require pierresh\simca
```

## How to use

Example to generate a SVG chart:

```php
use Pierresh\Simca\Charts\BarChart;

$chart = (new BarChart(600, 400))
	->setSeries([[10, 45, 30, 25], [15, 20, 15, 25], [5, 2, 10, 15]])
	->setLabels(['A', 'B', 'C', 'D'])
	->setColors(['#2dd55b', '#ffc409', '#0054e9'])
	->setOptions([
		'stacked' => true,
		'nbYkeys2' => 1,
	])
	->render();
```
```php
use Pierresh\Simca\Charts\LineChart;

$chart = (new LineChart(600, 400))
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
	])
	->render();
```

![examples](app/example.png)

Alternatively, you can replace `render()` with `renderBase64()` to get a base64 encoded SVG image.

## Development

There is a watcher script to automatically refresh the page when a change is made.

You will need to install [BrowserSync](https://browsersync.io/) first:

```bash
npm install -g browser-sync
```

Then the example page can be run with the following command:
```bash
./watcher.sh ./app/index.php
```

🧹 Run refactors using **Rector**
```bash
composer refactor
```

⚗️ Run static analysis using **PHPStan**:
```bash
composer stan
```

✅ Run unit tests using **PEST**
```bash
composer test
```

🚀 Run the entire quality suite:
```bash
composer quality
```