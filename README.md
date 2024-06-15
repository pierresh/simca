# Simca

SVG charts for PHP

This project aims to be a complete solution for creating SVG charts in PHP.

This is useful when charts need to be included in emails and PDFs.

The project is currently in progress, but the line chart feature is already available.

It will be available in Composer when it will be more complete.

## How to use

Example to generate a SVG chart:

```php
use Pierresh\Simca\Charts\LineChart;

$chart = (new LineChart(600, 400))
	->setSeries([[10, 45, 30, 25]])
	->setLabels(['A', 'B', 'C', 'D'])
	->addObjectiveY1(20, 'red', 1)
	->render();
```

![examples](app/example.png)

Alternatively, you can replace `render()` with `renderBase64()` to get a base64 encoded SVG image.

## Developement

There is a watcher script to automatically refresh the page when a change is made.

You will need to install [BrowserSync](https://browsersync.io/) first:

```bash
npm install -g browser-sync
```

Then the example page can be run with the following command:
```bash
./watcher.sh ./app/index.php
```