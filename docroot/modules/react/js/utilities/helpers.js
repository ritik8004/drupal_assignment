/**
 * Helper function to fix href.
 *
 * @param href
 *   The href to be fixed.
 * @return {string}
 *   The fixed href.
 */
const fixHref = (href) => (!href.startsWith('/') ? `/${href}` : href);

export default fixHref;
