/**
 * Helper function to check if Online Returns is enabled.
 */
const isOnlineReturnsEnabled = () => {
	if (typeof drupalSettings.onlineReturns !== 'undefined'
		&& typeof drupalSettings.onlineReturns.enabled !== 'undefined') {
		return drupalSettings.onlineReturns.enabled;
	}

	return false;
};

export default isOnlineReturnsEnabled;