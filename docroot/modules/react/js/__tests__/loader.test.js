import {Drupal} from './globals';
import {showFullScreenLoader, removeFullScreenLoader} from '../utilities/showRemoveFullScreenLoader';

describe('Full Loader', () => {
  beforeEach(() => {
    // Create the body element to be used on all scenarios.
    document.body = document.createElement("body");
  });

  describe('Tests for showFullScreenLoader', () => {
    it('Show full loader without contextual CSS', () => {
      showFullScreenLoader();
      // Check that full screen loader is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader"></div>');
    });

    it('Show full loader with contextual CSS', () => {
      showFullScreenLoader('foo');
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
    });

    it('Test full loader with multiple calls and make sure the markup doesn\'t change', () => {
      // First add contextual css.
      showFullScreenLoader('foo');
      // Then call again with no arguments.
      showFullScreenLoader('');
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
    });

    it('Show full loader with two contextual CSS', () => {
      // First add contextual css.
      showFullScreenLoader('foo');
      // Then add second contextual css.
      showFullScreenLoader('bar');
      // Check that full screen loader has all contextual classes.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo bar"></div>');
    });
  });

  describe('Tests for removeFullScreenLoader', () => {
    it('Test removing loader without contextual css', () => {
      // Show default loader.
      showFullScreenLoader();
      // Check that full screen loader is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader"></div>');
      // Remove loader.
      removeFullScreenLoader()
      // Check that loader is removed.
      expect(document.body.innerHTML).toEqual('');
    });

    it('Test not removing loader with contextual css', () => {
      // First add contextual css.
      showFullScreenLoader('foo');
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
      // Call remove loader without arguments.
      removeFullScreenLoader()
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
    });

    it('Test removing loader with contextual css', () => {
      // First add contextual css.
      showFullScreenLoader('foo');
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
      // Call remove loader without arguments.
      removeFullScreenLoader()
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
      // Remove second contextual css.
      removeFullScreenLoader('foo')
      // Check that loader is removed.
      expect(document.body.innerHTML).toEqual('');
    });

    it('Test fully removing loader with contextual css', () => {
      // First add contextual css.
      showFullScreenLoader('foo');
      // Check that full screen loader + foo class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo"></div>');
      // Then add second contextual css.
      showFullScreenLoader('bar');
      // Check that full screen loader has all contextual classes.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader foo bar"></div>');
      // Remove first contextual css.
      removeFullScreenLoader('foo')
      // Check that full screen loader + bar class is displayed.
      expect(document.body.innerHTML).toEqual('<div class="ajax-progress fullscreen-loader bar"></div>');
      // Remove second contextual css.
      removeFullScreenLoader('bar')
      // Check that loader is removed.
      expect(document.body.innerHTML).toEqual('');
    });
  });
});
