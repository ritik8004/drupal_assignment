# Alshaya SPC Middleware

This is a silex application that will be used as the middleware app for the
Single Page Checkout for the alshaya.

## How to use/setup on local :-
* ``vagrant ssh`` on alshaya VM.
* ``cd docroot/middleware``
* ``composer install``
* Middleware is ready for use on local.
* Check by just hitting url ``local.alshaya-hmkw.com/middleware/app.php/cart/{cart_id}``.
Here you can use any alshaya sitename. Cart id is the cart id to get the info.
