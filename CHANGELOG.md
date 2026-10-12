# Changelog

## 2.0.2 (2026-10-12)
- Improvement: compatible with Magento 2.4.6 (PHP 8.2).

## 2.0.1 (2026-10-12)
- Improvement: user guide screenshots and links updated for the CommerceGears name

## 2.0.0 (2026-10-11)
- Improvement: renamed to CommerceGears (new Composer package, admin menu and settings paths)

## 1.2.3 (2026-10-11)
- Fix: Next Numbers shows the number Magento gives next for Magento numbering, not the last number used.
- Improvement: every Next Numbers cell says whether it is a custom format or Magento numbering.
- Improvement: the Next Numbers table fits the settings page instead of scrolling in its own box.

## 1.2.2 (2026-10-09)
- New: product listing, FAQ and user guide.

## 1.2.1 (2026-10-07)
- Fix: the grid backfill command works on Magento 2.4.7 and 2.4.8.
- Fix: the README states the right CommerceGears Core version.

## 1.2.0 (2026-10-07)
- New: Deleted Orders Log keyword search by order number, admin user or customer email (hashed ones too).
- New: "Unmark Test Order" mass action.
- Improvement: clearer Deleted Orders Log with an introduction, fewer default columns and status labels.
- Improvement: titled confirmation for "Delete Permanently"; settings side by side with defaults named.

## 1.1.0 (2026-10-06)
- Improvement: requires CommerceGears Core instead of CommerceGears Base; menu and permissions move under "CommerceGears".

## 1.0.0 (2026-10-06)
- New: first release with custom order, invoice, shipment and credit memo numbers per store view.
- New: order grid columns for items, coupon code, shipping country, order comment and test order.
- New: test order flag, guarded order deletion (off by default) and a Deleted Orders Log.
- New: command line tools to show the numbering and to fill the grid columns for existing orders.
