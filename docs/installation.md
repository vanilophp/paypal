# PayPal Module Installation

1. Add to your application via composer:
    ```bash
    composer require vanilo/paypal 
    ```
2. Add the module to `config/concord.php`:
    ```php
    <?php
    return [
        'modules' => [
             //...
             Vanilo\Paypal\Providers\ModuleServiceProvider::class
             //...
        ]
    ]; 
    ```

---

**Next**: [Configuration &raquo;](configuration.md)
