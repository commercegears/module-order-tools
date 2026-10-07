# Softaware Order Tools

`softaware/module-order-tools` (Magento module `Softaware_OrderTools`). Free.

Small, frequently requested order utilities: your own order, invoice, shipment and credit memo numbers, extra
order grid columns, a test order flag and guarded order deletion with an audit log. Everything that changes
behaviour is off by default.

## Features

### Document numbers

- Per store view and per document type (order, invoice, shipment, credit memo): **prefix**, **suffix**,
  **minimum counter length** (zero padding), **start value** and **increment step**.
- Placeholders in prefix and suffix: `{store_id}` `{yyyy}` `{yy}` `{mm}` `{dd}` (store view's time zone).
  Otherwise letters, digits, `-`, `_` and `.` only (max. 20 characters each), so numbers are safe in URLs,
  PDFs, file names and CSV files.
- Invoices, shipments and credit memos can use **the order number** instead: `WEB-26-000123` for the first
  document, then `WEB-26-000123-1`, `-2`, ... for further documents of the same order.
- Optional **counter restart every year or month**. It is only applied when the number contains the period
  (`{yyyy}`/`{yy}` for yearly, plus `{mm}` for monthly), so a restarted counter can never repeat an earlier number.
  The admin refuses a reset setting that would not be safe.
- **Next Numbers** preview on the settings page (per store view, saved settings).
- Changing the settings never renumbers existing documents. Raising the start value makes a running counter jump
  to it; lowering it has no effect on a running counter.
- **Uniqueness**: the counter is taken with one atomic `INSERT ... ON DUPLICATE KEY UPDATE` on a separate database
  connection, so parallel checkouts never get the same value and the counter row is never locked for the length of
  an order or invoice transaction. Store views with the same prefix and suffix share one counter. Every candidate
  number is also checked against the existing documents (all store views) and skipped if taken; Magento's unique
  key on `increment_id` stays the final guard. If a custom number cannot be built, Magento's own number is used and
  the error is logged, so a checkout never fails because of the numbering.
- Built on Magento's sales sequence (plugin on `Magento\SalesSequence\Model\Manager`), no core rewrite.
  Magento's own sequence is not used while a custom format is active.

### Order grid extras

Optional columns (each switched on separately, with filters) in Sales > Orders, kept up to date by Magento's own
order grid indexer (synchronous or asynchronous):

| Column | Source |
|---|---|
| Items | "SKU x qty" of the order's top-level items |
| Coupon Code | `sales_order.coupon_code` |
| Shipping Country | shipping address country (select filter) |
| Order Comment | `sales_order.customer_note` (admin order comment or checkout comment modules) |
| Test Order | test order flag (see below) |

Payment method, shipping information and customer group are already available in Magento's own Columns menu,
so they are not duplicated. Grid columns are prefixed `sot_`.

### Test order flag

Mass actions **Mark as Test Order** / **Unmark Test Order** and a **Test Order** column with filter, so test
orders are easy to find (and, once cancelled, to delete). Off by default.

### Deleting orders (off by default)

- **Delete Permanently** mass action in the order grid and button on the order page, both with a confirmation
  (the grid's confirmation shows the number of selected orders). POST with form key only.
- Guards, all checked on the server for every order: the setting **Allow Deleting Orders**; the ACL resource
  **Softaware > Order Tools > Delete Orders** (not part of any role unless you add it; roles with "All" resources
  have it); the order's **current status** must be one of the allowed statuses (default: Canceled only), checked
  again with a row lock inside the delete transaction; at most **200 orders** per request.
- Deletes the order with its invoices, shipments, credit memos (through Magento's repositories, so their grid rows
  are removed too), items, addresses, payment, payment transactions, comments and tax rows. Each order is deleted
  in its own transaction together with its log entry.
- **Nothing is restored**: no stock, no refunds, no reward points or store credit. Downloadable links bought with the
  order are kept by Magento (unlinked from the order). Sales reports keep their aggregated figures until refreshed.
- **Deleted Orders Log** (Softaware > Order Tools > Deleted Orders Log): order number, order ID, store view, status,
  grand total, currency, customer email (stored as a SHA-256 hash by default, or in clear), test order flag, the
  deleted document numbers, order date, admin user, IP address and time. Read-only, with keyword search (order
  number, admin user or customer email; an email address also finds entries stored as a hash), filters and a CSV
  export (values that a spreadsheet would run as a formula are prefixed with an apostrophe). Entries cannot be
  deleted from the admin. While nothing has been deleted the page explains how deleting is set up.

## Where in the admin

- Settings: Stores > Configuration > Softaware > **Order Tools** (enabled switch, order grid, deleting orders;
  default scope) and **Order Tools: Document Numbers** (default / website / store view; the counter restart is
  default scope only). Also linked from Softaware > Order Tools.
- Softaware > Order Tools > Deleted Orders Log.
- ACL (Softaware > Order Tools): Delete Orders, View Deleted Orders Log, Mark Test Orders,
  Settings: Order Grid and Deleting, Settings: Document Numbers.

## Configuration

| Path | Default | |
|---|---|---|
| `softaware_ordertools/general/enabled` | 1 | Master switch for all features |
| `softaware_ordertools/grid/columns` | – | `sot_items,sot_coupon_code,sot_shipping_country,sot_customer_note` |
| `softaware_ordertools/grid/test_flag` | 0 | Test order column and mass actions |
| `softaware_ordertools/delete/enabled` | 0 | Allow deleting orders |
| `softaware_ordertools/delete/statuses` | canceled | Comma-separated status codes |
| `softaware_ordertools/delete/customer_email` | hash | `hash` or `keep` |
| `softaware_ordertools_numbers/<type>/mode` | default | `default`, `custom`, `order` (not for orders); type = order, invoice, shipment, creditmemo |
| `softaware_ordertools_numbers/<type>/prefix`, `.../suffix` | – | |
| `softaware_ordertools_numbers/<type>/length` | 6 | 1-10 |
| `softaware_ordertools_numbers/<type>/start`, `.../step` | 1, 1 | |
| `softaware_ordertools_numbers/<type>/reset` | never | `never`, `yearly`, `monthly` (default scope) |

Example (orders `WEB-26-000001`):

```
bin/magento config:set softaware_ordertools_numbers/order/mode custom
bin/magento config:set softaware_ordertools_numbers/order/prefix 'WEB-{yy}-'
bin/magento config:set softaware_ordertools_numbers/order/length 6
```

## CLI

```
bin/magento softaware:order-tools:sequence:show [--store=<id|code>]   # last and next numbers per store view (read-only)
bin/magento softaware:order-tools:grid:backfill [--batch-size=500]    # fill the grid columns for existing orders
```

Run the backfill once after installation; orders saved later are handled by Magento's grid indexer.

## Compatibility

Magento Open Source / Adobe Commerce 2.4.7–2.4.9, PHP 8.2–8.5. Requires `softaware/module-core` ^1.0.
No storefront code (numbers appear wherever Magento shows them, on Luma and Hyvä alike).

## Limitations

- Numbers are assigned when Magento asks for one. Like Magento's own sequence, a number taken by a checkout that
  then fails is not reused (gap in the series).
- Orders edited in the admin keep Magento's behaviour: the new order gets the old number plus `-1`, `-2`, ...
- "Same as the order number": if two invoices (or shipments, credit memos) of the same order are saved at exactly
  the same moment, one of them fails on Magento's unique key and has to be saved again.
- Counter restart applies to the whole installation (default scope), because store views with the same prefix and
  suffix share one counter.
- Deleting orders does not restore stock or reverse payments; cancel and refund first. The admin IP is taken from
  `REMOTE_ADDR` (configure the web server's real IP module behind a proxy or CDN).
- On Adobe Commerce, admin role scopes (restricting admins to some websites) were not tested with deletion; give
  the Delete Orders resource only to roles with access to all websites.
- With Enabled = No the `sot_*` columns and the counters stay; the extra grid columns are still filled by the
  grid indexer (cheap), just not shown.
