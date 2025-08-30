<?php

use Pierresh\Simca\Charts\Helper\Helper;

describe('format method', function () {
	test('formats integers without decimals by default', function () {
		expect(Helper::format(1000))->toBe('1,000');
		expect(Helper::format(1500))->toBe('1,500');
		expect(Helper::format(0))->toBe('0');
		expect(Helper::format(42))->toBe('42');
	});

	test('formats floats with 2 decimals by default', function () {
		expect(Helper::format(1000.5))->toBe('1,000.50');
		expect(Helper::format(1234.67))->toBe('1,234.67');
		expect(Helper::format(10.999))->toBe('11.00');
	});

	test('respects precision parameter', function () {
		expect(Helper::format(1234.5678, 3))->toBe('1,234.568');
		expect(Helper::format(1000, 2))->toBe('1,000.00');
		expect(Helper::format(42.123, 1))->toBe('42.1');
	});

	test('respects maxPrecision parameter and removes trailing zeros', function () {
		expect(Helper::format(1234.5000, null, 4))->toBe('1,234.5');
		expect(Helper::format(1000.0, null, 2))->toBe('1,000');
		expect(Helper::format(42.100, null, 3))->toBe('42.1');
	});

	test('handles negative numbers', function () {
		expect(Helper::format(-1000))->toBe('-1,000');
		expect(Helper::format(-42.5))->toBe('-42.50');
	});
});

describe('isColorDark method', function () {
	test('detects dark colors correctly', function () {
		expect(Helper::isColorDark('#000000'))->toBe(true);
		expect(Helper::isColorDark('#333333'))->toBe(true);
		expect(Helper::isColorDark('#404040'))->toBe(true);
	});

	test('detects light colors correctly', function () {
		expect(Helper::isColorDark('#ffffff'))->toBe(false);
		expect(Helper::isColorDark('#cccccc'))->toBe(false);
		expect(Helper::isColorDark('#aaaaaa'))->toBe(false);
	});

	test('handles edge cases', function () {
		expect(Helper::isColorDark(''))->toBe(false);
		expect(Helper::isColorDark('0'))->toBe(false);
	});

	test('handles medium gray values', function () {
		// #808080 has luma of approximately 128, which is the threshold
		expect(Helper::isColorDark('#808080'))->toBe(false);
		expect(Helper::isColorDark('#7f7f7f'))->toBe(true);
	});
});

describe('convertLabelToTimestamp method', function () {
	test('converts valid date strings to timestamps', function () {
		$timestamp = Helper::convertLabelToTimestamp('2024-01-01');
		
		expect($timestamp)->toBe(1704067200);
	});

	test('converts datetime strings to timestamps', function () {
		$timestamp = Helper::convertLabelToTimestamp('2024-01-01 12:00:00');
		
		expect($timestamp)->toBe(1704110400);
	});

	test('throws exception for invalid date strings', function () {
		expect(fn() => Helper::convertLabelToTimestamp('invalid-date'))
			->toThrow(Exception::class, 'invalid-date cannot be converted to a timestamp');
	});

	test('handles various date formats', function () {
		$formats = [
			'2024-12-25',
			'December 25, 2024',
			'25 Dec 2024',
			'2024/12/25',
		];

		foreach ($formats as $format) {
			$timestamp = Helper::convertLabelToTimestamp($format);
			expect($timestamp)->toBeInt();
			expect($timestamp)->toBeGreaterThan(0);
		}
	});
});

describe('convertOptions method', function () {
	test('returns options array unchanged', function () {
		$options = ['numLines' => 5, 'stacked' => true, 'unitY1' => 'kg'];
		$result = Helper::convertOptions($options);
		
		expect($result)->toBe($options);
		expect($result)->toEqual($options);
	});

	test('preserves all data types', function () {
		$options = [
			'string' => 'test',
			'int' => 42,
			'float' => 3.14,
			'bool' => true,
		];
		
		$result = Helper::convertOptions($options);
		
		expect($result['string'])->toBeString();
		expect($result['int'])->toBeInt();
		expect($result['float'])->toBeFloat();
		expect($result['bool'])->toBeBool();
	});

	test('handles empty array', function () {
		expect(Helper::convertOptions([]))->toBe([]);
	});
});