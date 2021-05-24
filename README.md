# PayPal Payment Gateway Support for Vanilo

[![Tests](https://img.shields.io/github/workflow/status/vanilophp/paypal/tests/master?style=flat-square)](https://github.com/vanilophp/paypal/actions?query=workflow%3Atests)
[![Packagist Stable Version](https://img.shields.io/packagist/v/vanilo/paypal.svg?style=flat-square&label=stable)](https://packagist.org/packages/vanilo/paypal)
[![StyleCI](https://styleci.io/repos/344426533/shield?branch=master)](https://styleci.io/repos/344426533)
[![Packagist downloads](https://img.shields.io/packagist/dt/vanilo/paypal.svg?style=flat-square)](https://packagist.org/packages/vanilo/paypal)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

This library enables [PayPal](https://developer.paypal.com/docs/business/checkout/server-side-api-calls/)
for [Vanilo Payments](https://vanilo.io/docs/master/payments).

Being a [Concord Module](https://konekt.dev/concord/1.9/modules) it is intended to be used by
Laravel Applications.

## Documentation

Refer to the markdown files in the [docs](docs/) folder.

## To-do

- [ ] Distinguish order and payment webhooks (resource.id differs!)
- [ ] Thrown custom exceptions on PayPal 4XX errors
- [ ] Test a situation when the amount is higher than the test accounts available credit
- [ ] Handle the case when neither webhooks are set up, nor front return happens:
    - [ ] timeout?
    - [ ] captured already?
- [ ] Log webhook/return facts in payment history
- [ ] Log auth before capture
- [ ] Add payer and shipping data to the paypal order
- [ ] Handle partial payments (via payments/captures)
- [ ] Auto-register webhooks
- [ ] Handle refunds (via Payments API)