jest.mock('axios');
import axios from 'axios';
import { callMagentoApi } from '../../../../js/backend/v2/common';

describe('Cart', () => {
  describe('Calls to Magento API', () => {

    beforeEach(() => {
      window.drupalSettings = {
        cart: {
          url: 'v1',
          store: 'en_gb',
        },
      };
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    it('Test empty response data', async () => {
      axios.mockResolvedValue({ });
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
          url: 'v1/en_gb/cart',
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
          message: 'Not found',
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
          url: 'v1/en_gb/cart',
        },
      ]);
    });

    it('Test response for cart quantity mismatch', async () => {
      axios.mockResolvedValue({ data: { message: 'Invalid cart data', error_code: 9010 }, status: 400 });
      const result = await callMagentoApi('/cart', 'POST', {});
      expect(result).toEqual({
        data: {
          error: true,
          error_code: 9010,
          error_message: 'Invalid cart data',
          message: 'Invalid cart data',
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
          message: 'Something is wrong',
        },
        status: 405,
      });
      expect(axios).toHaveBeenCalled();
    });

  });
});
