# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Deus AI Token Fee Guru
- Client-based architecture with fluent interface
- Support for multiple LLM models (GPT-5, GPT-4.1 series)
- Cache-aware token cost calculations
- Multi-user and conversation scaling
- Model comparison functionality
- CLI interface with interactive mode
- Multi-language support (English, Traditional Chinese)
- Comprehensive test suite
- PSR-4 compliant autoloading
- GitHub Actions CI/CD pipeline

### Features
- **Client Interface**: Main `Client` class with fluent method chaining
- **Calculator System**: Pluggable calculator architecture
- **Data Sources**: CSV-based pricing data with extensible data source interface
- **Request Options**: Type-safe configuration value object
- **Exception Handling**: Custom exception hierarchy
- **Utility Functions**: Helper functions for quick calculations
- **CLI Tool**: Full-featured command-line interface
- **Testing**: Unit and integration test coverage
- **Documentation**: Comprehensive README and examples

### Architecture
- Interface-based design for extensibility
- Dependency injection for flexibility
- Single responsibility principle
- Type safety with PHP 7.2+ features
- PSR compliance where applicable

## [1.0.0] - TBD

### Added
- First stable release