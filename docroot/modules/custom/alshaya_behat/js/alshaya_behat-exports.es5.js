// userEvent allows simulating real events on inputs.
// This is useful when the React components work with states
// that change when real events on imputs happen.
// see https://github.com/testing-library/user-event
import userEvent from '@testing-library/user-event';

// Make userEvent functions available in the AlshayaBehat scope.
// Usage example:
//  let input = document.querySelector('.field input'); // get the input field
//  AlshayaBehat.userEvent.type(input, '1') // change value
global.AlshayaBehat = { userEvent };
