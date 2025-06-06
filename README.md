# PayPal Payment Gateway for Vanilo

[![Tests](https://img.shields.io/github/actions/workflow/status/vanilophp/paypal/tests.yml?branch=master&style=flat-square)](https://github.com/vanilophp/paypal/actions?query=workflow%3Atests)
[![Packagist Stable Version](https://img.shields.io/packagist/v/vanilo/paypal.svg?style=flat-square&label=stable)](https://packagist.org/packages/vanilo/paypal)
[![StyleCI](https://styleci.io/repos/344426533/shield?branch=master)](https://styleci.io/repos/344426533)
[![Packagist downloads](https://img.shields.io/packagist/dt/vanilo/paypal.svg?style=flat-square)](https://packagist.org/packages/vanilo/paypal)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

> [!CAUTION]
> This is the development version (v3.x) of this module.
> It is a heavy work in progress and not meant to be used
> for any application in its current phase. Use version 2.1 (branch `2.x`)
> for your application until v3.0 gets released

This library enables [PayPal](https://developer.paypal.com/docs/business/checkout/server-side-api-calls/)
for [Vanilo Payments](https://vanilo.io/docs/master/payments).

Being a [Concord Module](https://konekt.dev/concord/1.x/modules) it is intended to be used by
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
