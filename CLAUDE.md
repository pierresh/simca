# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Simca is a PHP library for generating SVG charts. It supports Bar Charts, Line Charts, Area Charts, Pie Charts, Bubble Charts, and Radar Charts. Charts are generated server-side as SVG, making them ideal for emails and PDFs.

## Development Commands

### Quality Assurance
- `composer quality` - Run the complete quality suite (PHPStan, Rector dry-run, Prettier, tests)
- `composer stan` - Static analysis with PHPStan (level: max)
- `composer refactor` - Refactor code with Rector
- `composer format` - Format code with Prettier
- `composer test` - Run tests with PEST in watch mode
- `composer test-one-run` - Run tests once without watch mode

### Development Setup
- `composer install` - Install PHP dependencies
- `npm install` - Install Node.js dependencies (Prettier, Husky)
- `./watcher.sh ./app/index.php` - Start development server with BrowserSync

## Architecture

### Chart System
The library follows an object-oriented design with abstract base classes and interfaces:

- **AbstractChart** (`src/Charts/AbstractChart.php`) - Base class for all chart types with common functionality
- **Chart Types**: BarChart, LineChart, PieChart, BubbleChart, RadarChart in `src/Charts/`
- **Axis System**: Abstracted with interfaces
  - X-Axis: `XAxisInterface` with implementations for standard and time scales
  - Y-Axis: `YAxisInterface` with standard implementation
- **Adapters**: SVG rendering adapters in `src/Adapter/` (Circle, Line, Path, Rect, SVG, Text)
- **Models**: Core data structures (Dot, Objective) in `src/Model/`

### Key Patterns
- Interface-based axis system allows for different scale types (standard, time)
- Adapter pattern for SVG element creation
- Template method pattern in AbstractChart for rendering pipeline
- Charts support options like stacking, dual Y-axes, time scales, objectives, and events

### Testing
- Uses PEST testing framework
- Tests organized in `tests/Feature/` and `tests/Unit/`
- PHPUnit XML configuration includes both `src/` and `app/` directories

## Code Quality Tools
- **PHPStan**: Maximum level static analysis
- **Rector**: Automated refactoring with UP_TO_PHP_81, CODE_QUALITY, DEAD_CODE, EARLY_RETURN rules
- **Prettier**: Code formatting for PHP files
- **Husky + lint-staged**: Pre-commit hooks running syntax check, PHPStan, Rector, Prettier, and tests

## Dependencies
- **Runtime**: PHP 8.2+, meyfa/php-svg for SVG generation
- **Development**: PEST for testing, PHPStan for analysis, Rector for refactoring