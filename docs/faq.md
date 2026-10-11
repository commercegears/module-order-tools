# Order Tools for Magento 2: FAQ

**Does Order Tools change anything when I install it?**
No. Document numbers stay on Magento's default, no extra grid columns are shown, the test order flag is off and
deleting orders is off. You switch on each feature yourself under **Stores > Configuration > CommerceGears > Order
Tools** and **Order Tools: Document Numbers**.

**Will my existing order and invoice numbers change?**
No. The settings only apply to documents created afterwards. Existing orders, invoices, shipments and credit memos
keep their numbers.

**Can two orders get the same number?**
The counter is taken with a single atomic database statement on a separate connection, so parallel checkouts never
get the same value. Every candidate number is also checked against the existing documents of all store views and
skipped if it is taken, and Magento's unique key on the number stays the final guard. If a custom number cannot be
built, Magento's own number is used and the error is logged, so a checkout never fails because of the numbering.

**Can the counter start again every year or every month?**
Yes, with **Restart Counter**. The restart is only applied when the number contains the period (`{yyyy}` or `{yy}`
for yearly, plus `{mm}` for monthly), so a restarted counter can never repeat an earlier number. The admin refuses a
setting that would not be safe.

**Can invoices use the same number as the order?**
Yes. Set the invoice, shipment or credit memo **Numbering** to **Same as the order number**. The first document gets
the order number, further documents of the same order get `-1`, `-2` and so on.

**Can I use different numbers per store view?**
Yes. The numbering settings can be set per website and store view. Store views with the same prefix and suffix
share one counter; use `{store_id}` or a different prefix to give each store view its own series.

**Which columns does it add to the order grid?**
Items (SKU x qty), Coupon Code, Shipping Country, Order Comment and, with the test order flag, Test Order. Payment
method, shipping information and customer group are already in Magento's own **Columns** menu, so they are not
duplicated. Run `bin/magento commercegears:order-tools:grid:backfill` once to fill the columns for older orders.

**How is deleting orders protected?**
Several guards, all checked on the server for every order: **Allow Deleting Orders** must be switched on (it is
off by default); the admin role needs the separate permission **CommerceGears > Order Tools > Delete Orders**; the
order's current status must be one of the statuses you allow (default: Canceled only), and it is checked again with
a row lock just before the order is deleted; at most 200 orders per request; only POST requests with a valid form
key; and a confirmation dialog in the grid and on the order page.

**What exactly is deleted, and what is restored?**
The order with its invoices, shipments, credit memos, items, addresses, payment, payment transactions, comments and
tax rows, and their grid rows. Nothing is restored: no stock, no refunds, no reward points or store credit.
Cancel and refund an order first if you need that. Sales reports keep their figures until they are refreshed.

**Is there a record of deleted orders?**
Yes. **CommerceGears > Order Tools > Deleted Orders Log** lists every deleted order with its number, store view,
status, grand total, the deleted document numbers, order date, admin user, IP address and time. It is read-only,
can be searched and exported as CSV, and entries cannot be deleted from the admin.

**Is the customer email kept in the log?**
By default only a SHA-256 hash of it is stored, which suits deletions made for data-protection requests. You can
still search the log by email address: the search also finds entries stored as a hash. If you prefer, choose
**Store the email address** in the settings.

**Does it work with Hyvä?**
Order Tools has no storefront code. The numbers appear wherever Magento shows them (emails, My Account, PDFs), on
Luma, Hyvä and any other theme alike.

**Is Order Tools free?**
Yes, Order Tools is free. It requires `commercegears/module-core`, which Composer installs automatically.
