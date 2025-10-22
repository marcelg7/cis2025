# Testing Setup Documentation

## Overview
Comprehensive testing has been added to the Hay CIS project using PHPUnit with SQLite in-memory database.

**✅ Status**: Core testing infrastructure is complete and production-safe. Tests are fully isolated from the production MySQL database.

**⚠️ Note**: Some tests may fail due to SQLite schema compatibility issues (see Known Issues below). This does not affect the safety or functionality of the production application.

## What Was Added

### 1. Test Infrastructure
- **tests/TestCase.php** - Base test case with proper setup
- **tests/CreatesApplication.php** - Application bootstrapping with SQLite configuration
- **.env.testing** - Testing environment configuration

### 2. Model Factories
Created factories for test data generation:
- `ContractAddOnFactory.php` - Generate contract add-ons
- `ContractOneTimeFeeFactory.php` - Generate one-time fees
- `RatePlanFactory.php` - Generate rate plans with promo pricing
- `MobileInternetPlanFactory.php` - Generate mobile internet plans

### 3. Unit Tests

#### Contract Model Tests (`tests/Unit/ContractModelTest.php`)
- Contract creation and relationships
- Subscriber, activity type, commitment period relationships
- Add-ons and one-time fees relationships
- Total cellular rate calculation
- Financing requirement detection
- Total financed amount calculation
- Monthly device payment calculation
- DRO requirement detection
- Date and decimal field casting

#### Financial Calculations Tests (`tests/Unit/ContractFinancialCalculationsTest.php`)
- Device amount calculations
- Handling of zero and null agreement credits
- Total financed amount with all deductions
- Overpayment scenarios
- Monthly payment calculations (basic and complex)
- Early cancellation fee calculations
- Buyout cost calculations
- Remaining balance calculations
- Decimal precision handling

#### PDF Service Tests (`tests/Unit/ContractPdfServiceTest.php`)
- Service instantiation
- Contract relationship loading
- Financial calculation verification
- Early cancellation fee logic
- Buyout cost logic
- Monthly reduction calculations
- Null value handling
- Add-on and one-time fee cost calculations
- Total contract cost calculations

#### Rate Plan Tests (`tests/Unit/RatePlanTest.php`)
- Rate plan creation
- Effective price calculation (base vs promo)
- Promo detection
- Display name formatting
- Scopes: current, active, by type, by tier
- Contract relationships
- SOC code pricing lookup
- BYOD and SmartPay plan filtering
- Boolean and decimal field casting

#### Mobile Internet Plan Tests (`tests/Unit/MobileInternetPlanTest.php`)
- Plan creation
- Display name formatting
- Scopes: current, active, by category
- Contract relationships
- SOC code pricing lookup
- Current plans retrieval
- Field casting

### 4. Feature Tests

#### Contract Controller Tests (`tests/Feature/ContractControllerTest.php`)
- Index page display and listing
- Pagination
- Filtering by customer, device, and start date
- Create, show, and edit page display
- Contract creation (store)
- Contract update
- Contract deletion
- Guest access restrictions
- Relationship eager loading
- Required field validation
- Contracts with add-ons and one-time fees display

#### Permissions Tests (`tests/Feature/PermissionsTest.php`)
- Default permissions creation
- Default roles creation
- Admin role permissions
- User role assignment
- Permission checking
- Permission granting and revoking
- Role removal
- Multiple role assignment
- Permission caching
- Unique constraint validation

### 5. Database Configuration
- Tests now use SQLite in-memory database
- Configuration in `tests/CreatesApplication.php` ensures all tests use SQLite
- Fast, isolated test execution

## Migration Issues to Fix

Several migrations need to be updated to handle empty tables in tests. The pattern is to wrap `DB::table()->update()` calls with an existence check:

### Migrations that Need Fixing:

1. **2025_10_09_135123_remove_plan_id_from_contracts_table.php**
   ```php
   // Change:
   DB::table('contracts')->update(['plan_id' => null]);

   // To:
   if (DB::table('contracts')->exists()) {
       DB::table('contracts')->update(['plan_id' => null]);
   }
   ```

2. **2025_10_09_131329_remove_device_id_from_device_pricings_table.php**
   ```php
   if (DB::table('device_pricings')->exists()) {
       DB::table('device_pricings')->update(['device_id' => null]);
   }
   ```

3. **2025_10_09_125416_remove_device_id_from_contracts_table.php**
   ```php
   if (DB::table('contracts')->exists()) {
       DB::table('contracts')->update(['device_id' => null]);
   }
   ```

4. **2025_08_22_121812_update_contracts_table_with_new_values.php**
   ```php
   if (DB::table('contracts')->exists()) {
       DB::table('contracts')->update([...]);
   }
   ```

5. **2025_08_26_130145_add_is_test_to_contracts_and_subscribers.php**
   ```php
   if (DB::table('contracts')->exists()) {
       DB::table('contracts')->update(['is_test' => 1]);
   }
   if (DB::table('subscribers')->exists()) {
       DB::table('subscribers')->update(['is_test' => 1]);
   }
   if (DB::table('customers')->exists()) {
       DB::table('customers')->update(['is_test' => 1]);
   }
   if (DB::table('plans')->exists()) {
       DB::table('plans')->update(['is_test' => 1]);
   }
   ```

## Running Tests

### Run All Tests
```bash
vendor/bin/phpunit
```

### Run With Detailed Output
```bash
vendor/bin/phpunit --testdox
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature
```

### Run Specific Test File
```bash
vendor/bin/phpunit tests/Unit/ContractModelTest.php
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter="it_can_create_a_contract"
```

## Test Coverage Summary

### Models Covered
- Contract (comprehensive)
- RatePlan (comprehensive)
- MobileInternetPlan (comprehensive)
- ContractAddOn
- ContractOneTimeFee

### Services Covered
- ContractPdfService (financial calculations)

### Controllers Covered
- ContractController (CRUD operations)

### Features Covered
- Permissions and Roles (Spatie)
- Financial calculations
- PDF generation logic
- Contract workflows

## Known Issues

### SQLite Schema Compatibility

Some migrations that drop columns skip execution on SQLite due to compatibility issues. This causes foreign key references to remain in the schema, which can cause test failures. The following migrations skip on SQLite:

- `2025_10_09_115425_remove_shortcode_id_from_contracts_table`
- `2025_10_09_125416_remove_device_id_from_contracts_table`
- `2025_10_09_131329_remove_device_id_from_device_pricings_table`
- `2025_10_09_135123_remove_plan_id_from_contracts_table`

**Impact**: Some tests may fail due to missing tables or orphaned foreign keys.

**Workaround**: Tests are isolated to SQLite and will never affect your production MySQL database. The core infrastructure is solid.

**Long-term Solution**: Consider using a separate MySQL test database instead of SQLite for full schema compatibility.

## Next Steps

1. **Add More Controller Tests** - Test CellularPricingController, BellPricingController, etc.
2. **Add Integration Tests** - Test full contract creation workflows
3. **Add PDF Generation Tests** - Test actual PDF output (may require mocking)
4. **Increase Coverage** - Aim for 80%+ code coverage
5. **Consider Test MySQL Database** - For full schema compatibility (long-term)

## Benefits

- **Prevent Regressions**: Catch bugs before they reach production
- **Safe Refactoring**: Confidently refactor code knowing tests will catch breaks
- **Documentation**: Tests serve as living documentation
- **Faster Development**: Quickly verify changes work as expected
- **Code Quality**: Encourages better code structure

## Test Best Practices

1. **Arrange-Act-Assert**: Structure tests with clear setup, execution, and verification
2. **One Assertion Per Test**: Each test should verify one specific behavior
3. **Descriptive Names**: Test names should clearly describe what they test
4. **Independent Tests**: Tests should not depend on each other
5. **Fast Tests**: Keep tests fast by using in-memory database

## Notes

- All tests use SQLite in-memory database for speed and isolation
- Tests are configured to run migrations fresh for each test
- Factories provide realistic test data
- RefreshDatabase trait ensures clean state between tests
