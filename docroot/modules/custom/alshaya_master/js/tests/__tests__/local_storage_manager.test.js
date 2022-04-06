require ('../globals');
require ('../../local_storage_manager');
require ('../../common_functions');
import timer from '../timer';

describe('Local Store Manager', () => {
  describe('Test Drupal.runLocalStorageCleaner', () => {
    it('Make sure we delete only items set to expire', () => {
      // These values should not be removed from local storage.
      Drupal.addItemInLocalStorage('foo1', 'bar', 3);
      localStorage.setItem('foo_nan', NaN);
      localStorage.setItem('foo_null', null);
      localStorage.setItem('foo_array', ['foo']);
      localStorage.setItem('foo_int', 1);
      localStorage.setItem('foo_bool', true);
      localStorage.setItem('foo_str', 'bar');

      // This value should be removed from local storage
      Drupal.addItemInLocalStorage('foo2', 'bar', 1);

      timer( async () => {
        await Drupal.runLocalStorageCleaner();
        expect(Drupal.getItemFromLocalStorage('foo_nan')).toEqual('NaN');
        expect(Drupal.getItemFromLocalStorage('foo_null')).toEqual(null);
        expect(Drupal.getItemFromLocalStorage('foo_array')).toEqual('foo');
        expect(Drupal.getItemFromLocalStorage('foo_int')).toEqual(1);
        expect(Drupal.getItemFromLocalStorage('foo_bool')).toEqual(true);
        expect(Drupal.getItemFromLocalStorage('foo_str')).toEqual('bar');
        expect(Drupal.getItemFromLocalStorage('foo1')).toEqual('bar');
        expect(Drupal.getItemFromLocalStorage('foo2')).toEqual(null);
      }, 2);
    });
  })

  describe('Test Drupal.getItemFromLocalStorage', () => {

    it('With non-existing value', () => {
      Drupal.removeItemFromLocalStorage('foo_non_existing');
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(null);
    });

    it('With string value', () => {
      Drupal.addItemInLocalStorage('foo', 'bar', 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual('bar');
    });

    it('With integer value', () => {
      Drupal.addItemInLocalStorage('foo', 0, 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(0);
    });

    it('With true boolean value', () => {
      Drupal.addItemInLocalStorage('foo', true, 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(true);
    });

    it('With false boolean value', () => {
      Drupal.addItemInLocalStorage('foo', false, 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(false);
    });

    it('With arrays', () => {
      Drupal.addItemInLocalStorage('foo', ['bar'], 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(['bar']);
    });

    it('With objects', () => {
      localStorage.setItem('foo', {'foo': 'bar'});
      Drupal.addItemInLocalStorage('foo', {'foo': 'bar'}, 1);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual({"foo": "bar"});
    });

    it('Test non-expired items', () => {
      Drupal.addItemInLocalStorage('foo', 'bar', 10);
      timer( () => {
        const val = Drupal.getItemFromLocalStorage('foo');
        expect(val).toEqual('bar');
      }, 2);
    });

    it('Test expired items', () => {
      Drupal.addItemInLocalStorage('foo', 'bar', 1);
      timer( () => {
        const val = Drupal.getItemFromLocalStorage('foo');
        expect(val).toEqual(null);
      }, 2);
    });
  });

  describe('Test Drupal.getItemFromLocalStorage with data from other apps', () => {
    it('With string value', () => {
      localStorage.setItem('foo', 'bar');
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual('bar');
    });

    it('With null value', () => {
      localStorage.setItem('foo', null);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(null);
    });

    it('With NaN value', () => {
      localStorage.setItem('foo', NaN);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual('NaN');
    });

    it('With integer value', () => {
      localStorage.setItem('foo', 0);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(0);
    });

    it('With boolean value', () => {
      localStorage.setItem('foo', true);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(true);
    });

    it('With boolean value', () => {
      localStorage.setItem('foo', false);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual(false);
    });

    it('With arrays', () => {
      localStorage.setItem('foo', ['foo']);
      const val = Drupal.getItemFromLocalStorage('foo');
      expect(val).toEqual('foo');
    });
  });
});
