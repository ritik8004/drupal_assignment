import React from 'react';

const Notifications = ({ children }) => {
  const notificationTypes = {
    warning: [],
    alert: [],
  };

  /**
   * Helper function which returns new react element.
   */
  const createReactElement = (element, customprops = null) => {
    const eleprops = customprops !== null ? customprops : element.props;
    return React.createElement(
      element.type,
      { ...eleprops, key: element.type.name },
      null,
    );
  };

  // Convert children to array (incase, if there's only one child).
  const AllChildren = Array.isArray(children) ? children : [children];
  AllChildren.forEach((child) => {
    // For conditional child, we will receive false instad of child object.
    if (!child) {
      return;
    }

    if (child.props.type !== 'conditional') {
      notificationTypes[child.props.type].push(child);
    } else {
      // For conditional component do not show anything, if showAlert or showWarning
      // both conditions are false.
      if (!child.props.showAlert && !child.props.showWarning) {
        return;
      }

      const newType = (child.props.showAlert) ? 'alert' : 'warning';
      // Remove showAlert, showWarning from origin props and update type value.
      const { showAlert, showWarning, ...origProps } = { ...child.props };
      notificationTypes[newType].push(
        createReactElement(child, { ...origProps, type: newType }),
      );
    }
  });

  return (
    <div className="spc-warnings-alerts">
      <div className="spc-cart-item-warnings">
        {notificationTypes.warning.map((warningItem) => (
          <div className="spc-cart-item-warnings-item" key={warningItem.type.name}>
            { warningItem }
          </div>
        ))}
      </div>
      <div className="spc-cart-item-alerts">
        {notificationTypes.alert.map((alertItem) => (
          <div className="spc-cart-item-alerts-item" data-filled={alertItem.props.filled} key={alertItem.type.name}>
            { alertItem }
          </div>
        ))}
      </div>
    </div>
  );
};

export default Notifications;
