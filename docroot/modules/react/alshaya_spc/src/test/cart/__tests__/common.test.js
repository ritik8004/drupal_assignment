jest.mock('axios');
import axios from 'axios';
import utilsRewire from "../../../../js/backend/v2/common";
import { drupalSettings } from '../globals';
import * as cartData from '../data/cart.json';
import _ from 'lodash';

describe('Common', () => {
  describe('Functions from common.js', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    describe('Test formatCart()', () => {
      const formatCart = utilsRewire.__get__('formatCart');

      it('With Cart data', () => {
        const data = _.cloneDeep(cartData);
        const result = formatCart(data);
        expect(result.cart.customer).toEqual(undefined);
        expect(result.customer.addresses[0].region).toEqual('0');
        expect(result.customer.addresses[0].id).toEqual(undefined);
        expect(result.customer.addresses[0].customer_address_id).toEqual('69');
        expect(result.customer.addresses[0].street).toEqual(['1 London Rd']);
        expect(result.customer.addresses[1].street).toEqual(['17 crewdosn rd']);
        expect(result.shipping.method).toEqual('home_delivery');
        expect(result.shipping.type).toEqual('home_delivery');
        expect(result.shipping.clickCollectType).toEqual('home_delivery');
        expect(result.shipping.extension_attributes).toEqual(undefined);
        expect(result.cart.extension_attributes.shipping_assignments).toEqual(undefined);
        expect(result.payment).toEqual({});
      });

      it('Without customer address', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.customer.addresses;
        const result = formatCart(data);
        expect(result.customer.addresses).toEqual(undefined);
      });

      it('Without customer data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.customer;
        const result = formatCart(data);
        expect(result.customer).toEqual(undefined);
      });

      it('Without extension_attributes data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.extension_attributes;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
      });

      it('Without shipping data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.extension_attributes.shipping_assignments[0].shipping;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
      });

      it('Custom shipping method', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.method = 'foo';
        const result = formatCart(data);
        expect(result.shipping.method).toEqual('foo');
        expect(result.shipping.type).toEqual('home_delivery');
      });

      it('Click and collect', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.method = 'click_and_collect';
        const result = formatCart(data);
        expect(result.shipping.method).toEqual('click_and_collect');
        expect(result.shipping.type).toEqual('click_and_collect');
      });

      it('No shipping method', () => {
        const data = _.cloneDeep(cartData);
        data.cart.billing_address = { foo: 'bar' };
        delete data.cart.extension_attributes.shipping_assignments[0].shipping.method;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
        expect(result.cart.billing_address).toEqual({});
      });

      it('Store code', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.extension_attributes.store_code = '1234';
        const result = formatCart(data);
        expect(result.shipping.storeCode).toEqual('1234');
      });
    });
  });
});
