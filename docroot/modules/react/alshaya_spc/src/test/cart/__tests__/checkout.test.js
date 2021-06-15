jest.mock('axios');
import axios from 'axios';
import each from 'jest-each'
import utilsRewire, { getProcessedCheckoutData } from "../../../../js/backend/v2/checkout";
import { drupalSettings } from '../globals';
import * as cartData from '../data/cart.json';
import * as storeData_re1_4429_vif from '../data/store_RE1-4429-VIF.json';
import * as store_qatestsourcemap_mmcsp_740 from '../data/store_QATESTSOURCE_MMCSP-740.json';
import cncStoreList from '../data/cnc_stores_list.js';
import { getCncStores } from '../../../../js/backend/v2/checkout'

describe('Checkout', () => {
  describe('Checkout functions', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    const getMethodCodeForFrontend = utilsRewire.__get__('getMethodCodeForFrontend');
    each`
     input                           | expectedResult
     ${'foo'}                        | ${'foo'}
     ${'checkout_com_cc_vault'}      | ${'checkout_com'}
     ${'checkout_com_upapi_vault'}   | ${'checkout_com_upapi'}
   `.test('Test that getMethodCodeForFrontend($input) returns "$expectedResult"', ({ input, expectedResult }) => {
      expect(getMethodCodeForFrontend(input)).toBe(expectedResult);
    });

    it('Test formatShippingEstimatesAddress()', () => {
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const shipping_assignments = cartData.cart.extension_attributes.shipping_assignments;
      const address = [...shipping_assignments].shift().shipping.address;
      const result = formatShippingEstimatesAddress(address);
      expect(result).toEqual({
        email: 'osmarwado@gmail.com',
        firstname: 'Osmar',
        lastname: 'Wado',
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


    it('Test formatShippingEstimatesAddress() with extension attributes', () => {
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const shipping_assignments = cartData.cart.extension_attributes.shipping_assignments;
      const address = [...shipping_assignments].shift().shipping.address;

      // Add extension_attributes
      address.extension_attributes = {
        "attr1": "1",
        "attr2": "2",
      };

      // Remove custom_attributes
      delete (address.custom_attributes);

      const result = formatShippingEstimatesAddress(address);
      expect(result.custom_attributes).toEqual([
        {
          "attribute_code": "attr1",
          "value": "1"
        },
        {
          "attribute_code": "attr2",
          "value": "2"
        },
      ]);
    });

    it('Test formatAddressForFrontend()', async () => {
      const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
      const data = [
        {
          foo: 'bar',
        },
      ];
      const result = formatAddressForFrontend(data);
      expect(result).toEqual([{
        foo: 'bar',
      }]);
    });

    it('Test getCncStatusForCart()', async () => {
      const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
      const data = [
        {
          foo: 'bar',
        },
      ];
      const result = getCncStatusForCart(data);
      expect(result).toEqual([{
        foo: 'bar',
      }]);
    });

    describe('Test getStoreInfo()', () => {
      it('When proper store data parameter is provided', async () => {
        axios.mockResolvedValue({ data: storeData_re1_4429_vif, status: 200 });
        const getStoreInfo = utilsRewire.__get__('getStoreInfo');
        const result = await getStoreInfo({
          code: 'RE1-4429-VIF',
          distance: 25.766128033681,
          rnc_available: false,
          sts_available: true,
          sts_delivery_time_label: "1-2 days",
          low_stock: false,
          lead_time: null
        });

        expect(result.phone_number).toEqual('044190246 / 044190247');
        expect(result.code).toEqual(storeData_re1_4429_vif.code);
        expect(result.delivery_time).toEqual('1-2 days');
        expect(result.formatted_distance).toEqual(25.77);
        expect(axios).toHaveBeenCalled();
      });

      it('When provided store code is empty', async () => {
        axios.mockResolvedValue({ data: storeData_re1_4429_vif, status: 200 });
        const getStoreInfo = utilsRewire.__get__('getStoreInfo');
        const result = await getStoreInfo({
          code: '',
          distance: 25.766128033681,
          rnc_available: false,
          sts_available: true,
          sts_delivery_time_label: "1-2 days",
          low_stock: false,
          lead_time: null
        });
        expect(result).toEqual(null);
      });
    });

    describe('Test getCartStores()', () => {
      it('When proper store data parameter is provided', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: store_qatestsourcemap_mmcsp_740, status: 200 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios).toHaveBeenCalled();
        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].formatted_distance).toEqual(25.77);
      });

      it('When fetching 1 store info fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: [], status: 200 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].address).toBeUndefined();
      });

      it('When fetching the cnc store list fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 500 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios.mock.calls.length).toEqual(1);
        expect(result.length).toEqual(0);
      });
    });

    describe('Test getCncStores()', () => {
      it('When proper lat lon parameters is provided', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: store_qatestsourcemap_mmcsp_740, status: 200 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios).toHaveBeenCalled();
        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].formatted_distance).toEqual(25.77);
      });

      it('When lat is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(null, 20);

        expect(result).toEqual([]);
      });

      it('When lon is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, null);

        expect(result).toEqual([]);
      });

      it('When cart ID is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => null);

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(0);
        expect(result).toEqual(expect.objectContaining({
          'error': true,
          'error_code': 404,
        }));
      });

      it('When fetching 1 store info fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: [], status: 200 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].address).toBeUndefined();
      });

      it('When fetching the cnc store list fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 500 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(1);
        expect(result.length).toEqual(0);
      });
    });
  });
});
