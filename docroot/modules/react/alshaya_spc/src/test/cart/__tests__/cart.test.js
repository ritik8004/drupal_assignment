import StaticStorage from "../../../../js/backend/v2/staticStorage";

jest.mock('axios');
import axios from 'axios';
import * as cart from '../../../../js/backend/v2/cart';
import { callMagentoApi } from '../../../../js/backend/v2/common';
import { drupalSettings, Drupal } from '../globals';
import { getStorageInfo } from '../../../../js/utilities/storage';

describe('Cart', () => {
  beforeEach(() => {
    window.drupalSettings = drupalSettings;
  });

  afterEach(() => {
    StaticStorage.clear();
    jest.clearAllMocks();
  });

  describe('Calls to Magento API', () => {
    it('Test empty response data', async () => {
      axios.mockResolvedValue({ status: 200 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 500,
          error_message: 'Sorry, something went wrong and we are unable to process your request right now. Please try again later.',
        },
      });

      expect(axios).toHaveBeenCalled();

      expect(axios.mock.calls[0]).toEqual([
        {
          method: 'POST',
          headers: {
            'Alshaya-Channel': 'web',
            'Content-Type': 'application/json',
          },
          url: '/rest/kwt_en/cart',
        },
      ]);
    });

    it('Test response for 200 status', async () => {
      axios.mockResolvedValue({ data: { cart: { cart_id: 1234 } }, status: 200 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          cart: {
            cart_id: 1234,
          },
        },
        status: 200,
      });

      expect(axios).toHaveBeenCalled();
    });

    it('Test response for 200 status with errors', async () => {
      axios.mockResolvedValue(
        {
          data: {
            messages: {
              error: [
                {
                  code: 123,
                  message: 'Some problem',
                },
                {
                  code: 124,
                  message: 'Another problem',
                },
              ],
            },
          },
          status: 200,
        },
      );
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 123,
          error_message: 'Some problem',
        },
        status: 200,
      });

      expect(axios).toHaveBeenCalled();
    });

    it('Test response for 404 error', async () => {
      axios.mockResolvedValue({ data: { message: 'Not found' }, status: 404 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 404,
          error_message: 'Not found',
        },
        status: 404,
      });
      expect(axios).toHaveBeenCalled();
    });

    it('Test response for 500 error', async () => {
      axios.mockResolvedValue({ data: {}, status: 500 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 600,
          error_message: 'Back-end system is down',
        },
        status: 500,
      });

      expect(axios).toHaveBeenCalled();
    });

    it('Test response for > 500 error', async () => {
      axios.mockResolvedValue({ data: {}, status: 501 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 600,
          error_message: 'Back-end system is down',
        },
        status: 501,
      });

      expect(axios).toHaveBeenCalled();

      expect(axios.mock.calls[0]).toEqual([
        {
          method: 'POST',
          headers: {
            'Alshaya-Channel': 'web',
            'Content-Type': 'application/json',
          },
          url: '/rest/kwt_en/cart',
        },
      ]);
    });

    it('Test response for cart quantity mismatch', async () => {
      axios.mockResolvedValue({ data: { message: 'Invalid cart data', code: 9010 }, status: 400 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          code: 9010,
          error_code: 9010,
          error_message: 'Invalid cart data',
        },
        status: 400,
      });
      expect(axios).toHaveBeenCalled();
    });

    it('Test response for non 200 response', async () => {
      axios.mockResolvedValue({ data: { message: 'Something is wrong' }, status: 405 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 500,
          error_message: 'Something is wrong',
        },
        status: 405,
      });
      expect(axios).toHaveBeenCalled();
    });
  });

  describe('Test window.commerceBackend.createCart()', () => {
    it('Test with string value and response status 200', async () => {
      axios.mockResolvedValue({ data: 'ZYJ47012050MHZ', status: 200 });
      const result = await window.commerceBackend.createCart();
      expect(axios).toHaveBeenCalled();
      expect(result).toEqual('ZYJ47012050MHZ');
      expect(getStorageInfo('cart_id')).toEqual('ZYJ47012050MHZ');
    });

    it('Test with returning error and status 200', async () => {
      axios.mockResolvedValue({
        data: {
          error: true,
        },
        status: 200,
      });
      const result = await window.commerceBackend.createCart();
      expect(axios).toHaveBeenCalled();
      expect(result).toEqual(null);
      expect(getStorageInfo('cart_id')).toEqual(null);
    });

    it('Test with returning error and status 500', async () => {
      axios.mockResolvedValue({
        data: {
          error: true,
        },
        status: 500,
      });
      const result = await window.commerceBackend.createCart();
      expect(axios).toHaveBeenCalled();
      expect(result).toEqual(null);
      expect(getStorageInfo('cart_id')).toEqual(null);
    });
  });
});
