# Changelog

## 1.2.3 (2026-10-11)
- Fix: Next Numbers shows the number Magento gives next for Magento numbering, not the last number used.
- Improvement: every Next Numbers cell says whether it is a custom format or Magento numbering.
- Improvement: the Next Numbers table fits the settings page instead of scrolling in its own box.

## 1.2.2 (2026-10-09)
- New: product listing, FAQ and user guide.

## 1.2.1 (2026-10-07)
- Fix: the grid backfill command works on Magento 2.4.7 and 2.4.8.
- Fix: the README states the right Softaware Core version.

## 1.2.0 (2026-10-07)
- New: Deleted Orders Log keyword search by order number, admin user or customer email (hashed ones too).
- New: "Unmark Test Order" mass action.
- Improvement: clearer Deleted Orders Log with an introduction, fewer default columns and status labels.
- Improvement: titled confirmation for "Delete Permanently"; settings side by side with defaults named.

## 1.1.0 (2026-10-06)
- Improvement: requires Softaware Core instead of Softaware Base; menu and permissions move under "Softaware".

## 1.0.0 (2026-10-06)
- New: first release with custom order, invoice, shipment and credit memo numbers per store view.
- New: order grid columns for items, coupon code, shipping country, order comment and test order.
- New: test order flag, guarded order deletion (off by default) and a Deleted Orders Log.
- New: command line tools to show the numbering and to fill the grid columns for existing orders.
