# CIS Improvements & Feature Ideas

This document contains suggestions for improving the Contract Information System (CIS) to make life easier for CSRs and improve overall efficiency.

---

## Quick Wins (Easy to Implement)

### 1. Contract Templates/Presets ✅
- [x] Save common device + plan combinations as templates (v4.2025.160-161)
- [x] "Frequently used setups" that auto-fill common configurations
- [x] Would save CSRs from re-selecting the same iPhone + Plan combo repeatedly
- [x] Hybrid system: Personal + Team + Auto-generated from recent contracts

### 2. Customer Search Improvements ✅
- [x] Recently Viewed Customers quick access (already implemented)
- [x] Add phone number search (v4.2025.171)
- [ ] Add name/address search across customers

### 3. Bulk Operations
- [ ] Select multiple contracts and mark as signed at once
- [ ] Bulk export contracts for a date range
- [ ] Bulk status updates

### 4. Better Contract Validation ✅
- [x] Real-time validation as they fill out the form (v4.2025.171)
- [x] Highlight missing required fields before they try to save (v4.2025.171)
- [x] Warning if device pricing doesn't match selected plan tier (v4.2025.171)

### 5. Smart Defaults ✅
- [x] Remember CSR's last location selection (v4.2025.171)
- [ ] Auto-suggest most common plan for a device type
- [x] Pre-fill dates intelligently (start date = today, end date = +2 years) (v4.2025.171)

---

## Medium Effort (More Impactful)

### 6. Contract Cloning
- [ ] "Copy" button on existing contracts to create a new one with same settings
- [ ] Useful for family members getting same plan

### 7. Dashboard Improvements
- [ ] CSR-specific dashboard showing their contracts in progress
- [ ] Quick stats: "Your drafts today", "Pending signatures", "Ready to finalize"
- [ ] Color-coded status indicators

### 8. Keyboard Shortcuts
- [ ] Tab through form fields smoothly
- [ ] Ctrl+S to save draft
- [ ] Ctrl+Enter to advance to next step
- [ ] Quick contract search with Ctrl+K

### 9. Contract Notes/Comments
- [ ] Add internal notes to contracts visible only to CSRs
- [ ] "Customer requested call back", "Waiting for device delivery", etc.
- [ ] Would help with handoffs between CSRs

### 10. Mobile Responsive Improvements
- [ ] Some CSRs might use tablets at customer service desks
- [ ] Make signature capture work better on touchscreens

---

## Bigger Ideas (More Development)

### 11. Smart Contract Wizard
- [ ] Step-by-step guided flow for contract creation
- [ ] "What type of activation?" → "Choose device" → "Choose plan" → Review
- [ ] Reduces cognitive load and errors

### 12. Customer Portal Integration
- [ ] Send customers a link to sign contracts themselves
- [ ] They can review and e-sign from their phone
- [ ] Reduces back-and-forth with CSRs

### 13. Predictive Pricing
- [ ] "Customers who got this device usually choose this plan"
- [ ] Show most popular plan for each device tier
- [ ] AI suggestions based on customer's current plan

### 14. Automated Follow-ups
- [ ] Auto-send signature reminders to customers after X hours
- [ ] Email/SMS notifications when contract is ready
- [ ] Reduce manual follow-up work

### 15. Contract Timeline View
- [ ] Visual timeline showing contract progression
- [ ] "Created → Emailed → Signed → Finalized → Uploaded" with timestamps
- [ ] Helps track where bottlenecks are

---

## Quality of Life

### 16. Inline Help/Tooltips
- [ ] Hover tooltips explaining what "DRO" means, what "Hay Credit" is
- [ ] Training new CSRs would be easier

### 17. Undo Functionality
- [ ] "Oops, I picked the wrong device" - undo button
- [ ] Soft deletes with ability to restore

### 18. Better Error Messages
- [ ] Instead of "Validation failed", show exactly what's wrong and how to fix it
- [ ] Example: "The device you selected doesn't have pricing for the Ultra plan tier. Please choose a different device or change the plan."

---

## Reporting & Analytics

### 19. CSR Performance Dashboard
- [ ] Contracts created per day/week
- [ ] Average time to completion
- [ ] Error rates
- [ ] Helps identify training needs

### 20. Custom Reports
- [ ] "Show me all contracts created last month by device type"
- [ ] "Show me customers with contracts expiring next quarter"
- [ ] Export to Excel with one click

---

## Based on Recent Issues (Continuous Improvement)

### 21. Better Cross-Browser Testing
- [x] Fixed Firefox date picker issue (added "Today" button) - v4.2025.154
- [ ] Continue testing all features in Firefox, Chrome, Safari, Edge
- [ ] Automated cross-browser testing suite

### 22. More Intuitive Device Filtering
- [x] Fixed Basic devices not showing on all plans - v4.2025.153
- [ ] Continue refining device filtering logic based on CSR feedback

### 23. Better Error Handling
- [x] Fixed undefined variable error in customer fetch - v4.2025.155
- [ ] Add better error pages with "What you can do" suggestions
- [ ] Graceful degradation when APIs are slow/down

---

## Top Priority (Biggest Impact)

Based on analysis of CSR workflows and potential time savings:

### 🔥 1. Contract Templates
**Impact**: High | **Effort**: Low
- Would save the most time daily
- CSRs repeatedly create similar contracts
- Quick win with immediate ROI

### 🔥 2. Smart Contract Wizard
**Impact**: High | **Effort**: Medium
- Would reduce errors significantly
- Makes training new CSRs easier
- Guides users through complex process

### 🔥 3. Customer Portal for Signatures
**Impact**: Very High | **Effort**: High
- Would eliminate the biggest bottleneck
- Customers can sign on their own time
- Reduces CSR workload dramatically

---

## Recently Completed

- [x] Smart Defaults (location, dates) - v4.2025.171
- [x] Phone number search for customers - v4.2025.171
- [x] Real-time validation and required field highlighting - v4.2025.171
- [x] Device/Plan tier mismatch warnings - v4.2025.171
- [x] Fixed frequently used templates to filter non-current rate plans - v4.2025.170
- [x] Implemented Contract Templates system - v4.2025.160-161
- [x] Merged feedback Add Comment and Update buttons - v4.2025.158
- [x] Loosened authentication throttle limits - v4.2025.159
- [x] Fixed session expiration causing data loss - v4.2025.157
- [x] Fixed Slack notifications for feedback system - v4.2025.152
- [x] Added logging to feedback Slack notifications - v4.2025.151
- [x] Fixed basic devices filtering on all plan types - v4.2025.153
- [x] Added cross-browser "Today" button to date inputs - v4.2025.154
- [x] Fixed undefined variable in customer fetch - v4.2025.155

---

## Notes

- This list should be reviewed quarterly and prioritized based on CSR feedback
- Each item should be estimated for effort and impact before implementation
- Consider CSR input through surveys or feedback sessions
- Track which improvements lead to measurable time savings

**Last Updated**: December 5, 2025
