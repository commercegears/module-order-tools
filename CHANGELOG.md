# Changelog

## 1.2.0 (2026-10-07)

### Added

- Deleted Orders Log: keyword search by order number, admin user or customer email. An email address also finds
  entries where the email was stored as a hash, so you can check whether a given customer's order was deleted.

### Changed

- Deleted Orders Log: a one-line introduction above the grid; while nothing has been deleted, an explanation
  instead of an empty table that says whether deleting is switched on and which statuses can be deleted, with
  "Go to Orders" / "Open Settings". Fewer default columns (ID, order ID, currency, order date and IP address are
  under Columns), newest deletions first by Deleted At, status labels instead of status codes, hashed customer
  emails shown shortened (the CSV export keeps the codes and the full value).
- Order grid: the mass action "Remove Test Order Mark" is now "Unmark Test Order" (fits the actions menu).
- Order page: "Delete Permanently" opens a titled confirmation dialog ("Delete Order Permanently") whose button
  says "Delete Permanently" instead of "OK"; no inline JavaScript on the button.
- Settings: the two Order Tools sections sit next to each other in the Softaware tab; General, Next Numbers and
  Order Numbers open first; every comment names the default; shorter wording for Enabled and Allow Deleting
  Orders; Prefix and Suffix can be restored to the default and are checked for 20 characters in the browser.
- Next Numbers table: aligned cells (it used to inherit the form's field padding), "Last used" instead of "last",
  a warning when Order Tools is switched off, and the styles come from a small module CSS file that is loaded only
  on this settings section.

## 1.1.0 (2026-10-06)

### Changed

- Requires `softaware/module-core` instead of `softaware/module-base`. The admin menu and ACL now sit under `Softaware_Core::core` ("Softaware"); roles that had access keep it (migrated by module-core). After updating all SoftAware modules, `softaware/module-base` can be removed.

## 1.0.0 (2026-10-06)

### Added
- Custom order, invoice, shipment and credit memo numbers per store view: prefix, suffix, minimum counter length,
  start value, increment step, placeholders `{store_id}` `{yyyy}` `{yy}` `{mm}` `{dd}`, optional yearly or monthly
  counter restart (only when the number contains the period), "same as the order number" for invoices, shipments
  and credit memos, next-number preview, atomic counters with a duplicate check and fallback to Magento's number.
- Optional order grid columns: items (SKU x qty), coupon code, shipping country, order comment, test order; kept up
  to date by Magento's grid indexer.
- Test order flag with mass actions and grid filter.
- Guarded order deletion (off by default): separate ACL resource, allowed statuses (default Canceled), status
  re-checked under a row lock, 200 orders per request, POST with form key, confirmation dialogs.
- Deleted Orders Log (read-only) with formula-safe CSV export; customer email hashed by default.
- CLI: `softaware:order-tools:sequence:show`, `softaware:order-tools:grid:backfill`.
