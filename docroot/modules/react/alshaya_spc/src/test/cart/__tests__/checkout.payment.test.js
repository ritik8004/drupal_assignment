jest.mock('axios');
import axios from 'axios';
import { getPaymentMethods }  from '../../../../js/backend/v2/checkout.payment';
import { drupalSettings, Drupal } from '../globals';
import paymentMethods from '../data/paymentMethods';
import * as cartData from '../data/cart.json';

describe('Checkout Payment', () => {
  describe('Checkout Payment functions', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      // Clear and reset any mocks set by other tests.
      global.Drupal.alshayaSpc.staticStorage.clear();
      jest.clearAllMocks();
      jest.resetAllMocks();
    });

    describe('Test getPaymentMethods()', () => {
      it('With Shipping type for getPaymentMethods', async () => {
        window.commerceBackend.setRawCartDataInStorage(null);

        axios
          // Mock for getCart.
          .mockResolvedValueOnce({ data: cartData, status: 200 })
          // Mock for getPaymentMethods().
          .mockResolvedValueOnce({ data: paymentMethods, status: 200 });

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const result = await getPaymentMethods();

        expect(axios).toHaveBeenCalled();
        expect(result.length).toEqual(4);
        expect(result[0].code).toEqual('checkout_com_upapi_vault');
        expect(result[0].title).toEqual('Saved Cards (Checkout.com UPAPI)');
        expect(result[3].code).toEqual('cashondelivery');
        expect(result[3].title).toEqual('Cash On Delivery');
      });

      it('With null value when shipping method is not provided', async () => {
        window.commerceBackend.setRawCartDataInStorage(null);
        cartData.shipping = {
          method: {
            method: {},
          },
        };

        axios
          .mockResolvedValueOnce({ data: cartData, status: 200 })
          .mockResolvedValueOnce({ data: {}, status: 200 });
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const result = await getPaymentMethods();
        expect(result).toEqual({});
      });
    });

  });
});
