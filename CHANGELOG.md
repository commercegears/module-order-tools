# Changelog

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
