# CIS4 Project TODO List

## 🔴 High Priority

### NISC API Inventory Integration
- [ ] **Wait for NISC API Equipment Permissions** (Email sent: 2025-10-24)
  - Waiting on NISC administrator to grant equipment/inventory endpoint access
  - Current status: 403 Forbidden on `/facility-equipment` endpoint
  - Once approved, continue with:
    - [ ] Test equipment endpoints with new permissions
    - [ ] Design "Check Inventory" page for CSRs
    - [ ] Build searchable device inventory interface
    - [ ] Consider integration with Bell Devices table
  - Test page available at: `/inventory/test`

## 🟡 Medium Priority

### Reporting Enhancements
- [x] Add CSV export to all reports (Completed: 2025-10-24)
- [x] Fix customer name display (FirstName LastName) (Completed: 2025-10-24)
- [x] Fix device name field reference in reports (Completed: 2025-10-24)

## 🟢 Low Priority / Future Enhancements

### Features to Consider
- [ ] Additional report types based on user feedback
- [ ] Enhanced analytics dashboard
- [ ] Mobile-responsive improvements
- [ ] Email functionality for calculator comparisons (currently placeholder)

## ✅ Recently Completed Features

### Contract Calculator & Comparison Tool (Completed: 2025-10-24)
- [x] Side-by-side comparison of up to 4 rate plans
- [x] Device financing calculations (SmartPay, DRO, BYOD)
- [x] Mobile internet and add-ons integration
- [x] 24-month cost projections with Hay Credit savings
- [x] Save/load comparisons for future reference
- [x] PDF export functionality
- [x] Print-friendly comparison sheets
- [x] Interactive Alpine.js interface
- Available at: `/calculator`

## 📝 Notes

### Recent Completions
- 2025-10-24: Added CSV export functionality to Contract Summary, Device Sales, and Plan Adoption reports
- 2025-10-24: Fixed report display issues (customer names, device names)
- 2025-10-24: Created NISC API inventory test page at `/inventory/test`
- 2025-10-24: Implemented Contract Calculator & Comparison Tool
- 2025-10-24: Fixed calculator column name bugs (rate → effective_price/monthly_rate)
- 2025-10-24: Added credit_duration and credit_when_applicable columns to rate_plans table

### Technical Debt
- [ ] Review and optimize database queries in reports
- [ ] Add caching for frequently accessed NISC API data
- [ ] Consider background job for large report generation

---
*Last Updated: 2025-10-24*
