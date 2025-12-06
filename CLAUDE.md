# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suites
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Feature

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Package Development
```bash
# Install dependencies
composer update

# Generate package autoloader
composer dump-autoload

# Check code style
vendor/bin/phpcs --standard=PSR12 src/
```

## Architecture Overview

This is the **Eloquent IFRS** package - a comprehensive double-entry accounting system for Laravel applications that generates IFRS-compliant financial statements.

### Core Architecture

**Model Layer (`src/Models/`)**:
- **Entity**: Represents companies/organizations with reporting currencies and configurations
- **Account**: Chart of accounts with IFRS-compliant categorization (assets, liabilities, equity, revenue, expenses)
- **Transaction**: Base model for all financial transactions with double-entry validation
- **LineItem**: Individual transaction lines supporting VAT, quantities, and multi-currency
- **Ledger**: Immutable ledger entries with hashing for audit trail integrity
- **ReportingPeriod**: Period management with open/closed/adjusting states
- **Vat**: Configurable VAT rates with compound VAT support
- **Currency & ExchangeRate**: Multi-currency support with rate management

**Transaction Types (`src/Transactions/`)**:
All inherit from base Transaction model and implement specific business logic:
- **Client Transactions**: CashSale, ClientInvoice, CreditNote, ClientReceipt
- **Supplier Transactions**: CashPurchase, SupplierBill, DebitNote, SupplierPayment
- **Internal Transactions**: JournalEntry, ContraEntry

**Financial Reports (`src/Reports/`)**:
- **IncomeStatement**: P&L with operating/non-operating sections
- **BalanceSheet**: Statement of financial position with IFRS classification
- **CashFlowStatement**: Indirect method cash flow from operations, investing, financing
- **TrialBalance**: Account balance listing
- **AccountStatement/AccountSchedule**: Transaction history and outstanding balances
- **AgingSchedule**: Receivable/Payable aging analysis

**Key Interfaces & Traits**:
- **Assignable/Clearing**: Transaction assignment and clearance workflows
- **Recyclable**: Soft delete with audit trail
- **Segregating**: Entity-based data isolation
- **Buys/Sells**: Purchase/sales transaction behavior

### Configuration System

The package uses a comprehensive configuration system (`config/ifrs.php`):
- **Account Types**: IFRS-compliant account classification with configurable codes
- **Glossary**: Localization support for account/transaction/statement labels
- **Statement Sections**: Configurable mapping of account types to financial statement sections
- **Aging Brackets**: Customizable aging schedule brackets
- **Multi-entity**: Support for multiple companies with separate reporting periods

### Database Design

All tables use configurable `ifrs_` prefix to prevent collisions:
- **Core tables**: entities, accounts, transactions, line_items, ledgers
- **Supporting tables**: currencies, exchange_rates, vats, reporting_periods
- **Audit tables**: recycled_objects for soft delete tracking
- **Assignment tables**: transaction assignments and clearances
- **Enhancement tables**: cost_centers, transaction_schedules, applied_vats

### Filament Integration

The package includes Filament v4 admin panel resources:
- **AccountsResource**: Chart of accounts management
- **ReportingPeriodResource**: Period management
- **CostCenterResource**: Cost center tracking
- **IFRSPlugin**: Main plugin integration

### Security & Integrity

- **Ledger Immutability**: Posted transactions create hashed ledger entries
- **Double Entry Validation**: Automatic balance verification
- **Period Controls**: Closed periods prevent modifications
- **Entity Isolation**: Multi-tenant data separation
- **Audit Trail**: Full transaction history with recycling

## Package Context

This package is designed for Laravel applications requiring professional accounting capabilities:
- Multi-entity support with separate reporting periods
- IFRS-compliant financial statements
- Multi-currency with forex revaluation
- Comprehensive VAT/GST handling
- Professional audit trails and data integrity

The codebase follows Laravel conventions with extensive use of Eloquent relationships, events, and service providers for seamless integration.