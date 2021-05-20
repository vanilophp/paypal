# Notes To PayPal Internals

## PayPal Order ID vs Payment ID

PayPal can store Vanilo Payment Id as the `custom_id` field under
`purchase_units`.

## Frontend Returns and Server-to-Server Messaging

PayPal by default does not send messages to the server when certain
events happen to the orders (represented as payments in Vanilo).
This must be explicitly activated by setting up Webhooks in the PayPal
Dashboard.

PayPal uses two separate "return to merchant" (ie. frontend) URLs:

- Return URL: when a payment was successful
- Cancel URL: when a payment was unsuccessful

These, however are happening via the browser of the consumer, therefore
it's not possible to rely on that the request will ever hit the server.

As a consequence, setting up webhooks is vital from the reliability
perspective of PayPal payment handling.

## Webhooks Arrive Late

While many other gateways ensure that IPN (server-to-server) messages
are sent first before returning the consumer to the webshop's payment
return page, at PayPal this is not the case.

In most of the cases, the webhook from PayPal will hit your server later
than the consumer arrives back to the payment return page.

## Webhooks Are Not 100% Reliable

I have noticed the following scenario during testing (sandbox env):

- Create a PayPal Order **without return/cancel URLs**;
- Go to the approval URL in the browser, enter a valid card;
- The payment gets accepted, but PayPal redirects you to its payment page again;
- The Webhook with `"event_type":"CHECKOUT.ORDER.APPROVED"` gets sent to your server;
- Contrary, if you fetch the order via API it's still in `CREATED` status;
- If you attempt to capture the order, it gives an `ORDER_NOT_APPROVED` exception.

The above described phenomenon might be a shortcoming or a special
behavior of Sandbox accounts, but definitely rings to bell to not fully
rely on PayPal webhooks.

As a consequence, it is vital to **always fetch the status of an order**
from PayPal API and don't rely on the content of Webhooks Messages.

## No Decline Messages

PayPal does not send "decline" messages of failed payment attempts. This
applies to Webhooks in every case.

If a consumer can't complete the payment **AND** clicks the
_"Cancel, return to the merchant"_ button, then PayPal will redirect the
consumer to the "payment cancel" page, which is the "unsuccessful"
payment return page. There is however no guarantee that the consumer
will ever come to this route.

Regardless of whether the payment cancel page was hit or not, the PayPal
Order remains in `CREATED` state.

## No Refund Message

There's no possibility to partially refund an order with PayPal.

As part of dispute resolution, orders can be refunded, but there's no
webhook event for that, moreover, orders don't have `REFUNDED` status.

It means that with PayPal it's only possible to get a feedback if an
order was refunded via the Payments API (and not the Checkout Order API)

## Approved (Authorized) Orders Don't Report the Amount

Once an order gets approved by the consumer, the order goes into the
`APPROVED` status. In this case however, the `purchase_units` object
of the response doesn't contain `payments` member. Once the Order gets
captured and you refetch the order from the api, the `payments` field
gets populated:

```json
{
  "id": "B7S1678G7339841G",
  "status": "COMPLETED",
  "purchase_units": [
    {
      "reference_id": "default",
      "shipping": {},
      "payments": {
        "captures": [
          {
            "id": "5W189T70Q7364B948",
            "status": "COMPLETED",
            "amount": {
              "currency_code": "EUR",
              "value": "9.99"
            },
            "final_capture": true,
            "seller_protection": {
              "status": "ELIGIBLE",
              "dispute_categories": ["ITEM_NOT_RECEIVED", "UNAUTHORIZED_TRANSACTION"]
            },
            "seller_receivable_breakdown": {
              "gross_amount": {
                "currency_code": "EUR",
                "value": "9.99"
              },
              "paypal_fee": {
                "currency_code": "EUR",
                "value": "0.54"
              },
              "net_amount": {
                "currency_code": "EUR",
                "value": "9.45"
              }
            },
            "custom_id": "JAnIcNK1eA2Fuu2NnwcFc",
            "links": ["..."],
            "create_time": "2021-05-20T17:22:27Z",
            "update_time": "2021-05-20T17:22:27Z"
          }
        ]
      }
    }
  ],
  "payer": {},
  "links": ["..."]
}
```