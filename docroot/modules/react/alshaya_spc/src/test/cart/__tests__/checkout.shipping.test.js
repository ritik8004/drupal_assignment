jest.mock('axios');
import axios from 'axios';
import utilsRewire  from '../../../../js/backend/v2/checkout.shipping';
import { getCart } from '../../../../js/backend/v2/common';
import { drupalSettings, Drupal } from '../globals';
import * as cartData from '../data/cart.json';

describe('Checkout Shipping', () => {
  describe('Checkout Shipping functions', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      // Clear and reset any mocks set by other tests.
      global.Drupal.alshayaSpc.staticStorage.clear();
      jest.clearAllMocks();
      jest.resetAllMocks();
    });

    it('Test formatShippingEstimatesAddress()', async () => {
      axios.mockResolvedValueOnce({ data: cartData, status: 200 });
      jest
        .spyOn(window.commerceBackend, 'getCartId')
        .mockImplementation(() => '1234');
      const response = await getCart();
      const address = response.data.shipping.address;
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const result = formatShippingEstimatesAddress(address);
      expect(result).toEqual({
        email: 'osmarwado@gmail.com',
        firstname: 'Osmar',
        lastname: 'Wado',
        street: [
          '1 London Rd',
        ],
        telephone: '+971555666777',
        country_id: 'AE',
        city: 'Al Awir',
        custom_attributes: [
          {
            attribute_code: 'address_city_segment',
            value: '1',
          },
          {
            attribute_code: 'area',
            value: '13',
          },
        ],
      });
    });

    it('Test formatShippingEstimatesAddress() with extension attributes', async () => {
      axios.mockResolvedValueOnce({ data: cartData, status: 200 });
      jest
        .spyOn(window.commerceBackend, 'getCartId')
        .mockImplementation(() => '1234');

      const response = await getCart();
      const address = response.data.shipping.address;
      // Add extension_attributes
      address.extension_attributes = {
        attr1: '1',
        attr2: '2',
      };
      // Remove custom_attributes
      delete (address.custom_attributes);

      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const result = formatShippingEstimatesAddress(address);
      expect(result.custom_attributes).toEqual([
        {
          attribute_code: 'attr1',
          value: '1'
        },
        {
          attribute_code: 'attr2',
          value: '2'
        },
      ]);
    });

  });
});
