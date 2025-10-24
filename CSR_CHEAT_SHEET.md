# CSR Cheat Sheet - Contract Information System

## Getting Started

### First Time Login
1. Check your email for password reset link
2. Click the link and create your password
3. Log in at the CIS web address
4. Your assigned location should already be set in your profile

---

## Creating a New Contract

### Step 1: Find the Customer
1. Click **"Customers"** in the top navigation
2. Enter customer account number or name in search
3. Click **"Fetch from NISC"** if customer not found locally
4. Click on the customer name to view their account

### Step 2: Select the Subscriber
- View the customer's mobility accounts and subscribers
- Click **"Create Contract"** next to the subscriber you want

### Step 3: Fill Out Contract Details
**Basic Information:**
- **Start Date**: Contract start date (usually today)
- **End Date**: Usually 24 months from start date
- **Activity Type**: New Activation, Upgrade, etc.
- **Location**: Should auto-select to your store location
- **Customer Phone**: Select from available phone numbers

**Device Information (if selling a device):**
- **Pricing Type**: SmartPay, DRO, or BYOD
- **Device**: Search and select the device
- **Tier**: Ultra, Max, Select, or Lite
- **Pricing**: Should auto-populate based on device/tier selection

**Rate Plan:**
- Select the cellular rate plan
- Add mobile internet plan if needed
- Select any add-ons (voicemail, call display, etc.)

**Financial Details:**
- **Agreement Credit**: Device discount/credit amount
- **Upfront Payment**: Required payment at time of sale
- **Down Payment**: Optional additional payment
- **Deferred Payment**: First payment deferred to later
- **Commitment Period**: Usually 24 months
- **First Bill Date**: Usually next billing cycle

### Step 4: Review and Save
1. Click **"Create Contract"** at the bottom
2. Contract is saved as **"Draft"** status

---

## Signing Workflow

### Customer Signs the Main Contract
1. Open the contract from the contracts list
2. Click **"Sign Contract"**
3. Hand device to customer for signature
4. Click **"Finalize Contract"** when signature is complete

### Financing Form (if device has financing)
1. After main contract is signed, system will prompt for financing form
2. Click **"Sign Financing Agreement"**
3. Customer signs the financing terms
4. **CSR Initial**: You must also initial the financing form
5. Click **"Finalize Financing"**

### DRO Form (if device has Device Return Option)
1. Similar to financing workflow
2. Click **"Sign DRO Agreement"**
3. Customer signs
4. **CSR Initial**: You must also initial the DRO form
5. Click **"Finalize DRO"**

---

## Editing an Existing Contract

### When Can I Edit?
- Contracts in **"Draft"** or **"Pending"** status can be edited
- Signed/completed contracts require creating a revision

### How to Edit:
1. Find the contract in the **Contracts** list
2. Click **"Edit"**
3. Make your changes
4. Click **"Update Contract"**

### Creating a Revision:
- If contract is already signed but needs changes
- Click **"Create Revision"** on the contract view
- This creates a new draft with the same information

---

## Common Tasks

### Searching for Contracts
- Use the global search bar at the top
- Search by customer name, account number, or phone

### Downloading/Emailing Contracts
1. Open the contract view
2. Click **"Download PDF"** to save locally
3. Click **"Email Contract"** to send to customer
4. Click **"Send to Vault"** to upload to FTP server

### Viewing Your Activity
- Click your name in top right → **"My Activity Logs"**
- Shows all contracts you've created/modified

---

## Troubleshooting

### Customer Not Found
- Make sure you're searching by the correct account number
- Try clicking **"Fetch from NISC"** to pull latest data
- Check spelling if searching by name

### Device Not Showing in List
- Make sure device pricing has been uploaded by admin
- Try searching by manufacturer or model name
- Contact admin if device is missing

### Location Dropdown Empty
- Contact admin - locations need to be created first
- Your user account needs a location assigned

### Signature Not Saving
- Make sure customer actually signed (not just touched screen)
- Check internet connection
- Try refreshing and signing again

### "Location: N/A" Showing on Contract
- Edit the contract and select your location from dropdown
- Save the contract
- This was fixed in recent update

---

## Reporting Bugs/Issues

### How to Report a Bug
1. Click your name in top right → **"Report a Bug"**
2. Fill out the form with:
   - **Title**: Short description of the issue
   - **Priority**: How urgent is it?
   - **Description**: Detailed explanation of what happened
   - **Steps to Reproduce**: How to make the bug happen again
   - **Expected vs Actual**: What should happen vs what did happen
3. Click **"Submit Report"**

### What Makes a Good Bug Report?
- **Be specific**: "Contract won't save" vs "Error when clicking Save on contract edit page"
- **Include details**: What were you doing? What customer/contract?
- **Screenshots help**: Take a photo of error messages
- **Note the time**: When did it happen?

---

## Tips & Best Practices

### Before Creating Contract:
- ✅ Verify customer information is correct
- ✅ Confirm device is in stock
- ✅ Double-check rate plan pricing
- ✅ Confirm commitment period with customer

### During Contract Creation:
- 💾 Save your work frequently
- 📱 Have customer verify their phone number
- 💰 Review all pricing with customer before signing
- 📋 Explain any financing terms clearly

### After Contract Completion:
- ✉️ Email copy to customer
- 📤 Send to Vault FTP (if required)
- 🗄️ File any physical paperwork per store policy
- ✅ Mark in your external systems (if applicable)

---

## Quick Reference

### Contract Statuses
- **Draft**: Contract created but not signed
- **Pending**: Awaiting signature
- **Signed**: Customer has signed
- **Completed**: Fully processed and finalized

### Activity Types
- **New Activation**: Brand new customer
- **Upgrade**: Existing customer getting new device
- **Hardware Upgrade**: Device only, no plan change
- **Plan Change**: Rate plan modification
- **Add-a-Line**: Adding line to existing account

### Pricing Types
- **SmartPay**: Device financing over 24 months
- **DRO**: Device Return Option (can return device early)
- **BYOD**: Bring Your Own Device (no device purchase)

---

## Getting Help

- **Technical Issues**: Report via bug reporting system
- **Process Questions**: Ask your supervisor
- **System Training**: Review this cheat sheet and practice in test mode
- **Emergency**: Contact IT support

---

**Version**: v4.2025.60
**Last Updated**: October 24, 2025
**Need to update this guide?** Contact your system administrator.
