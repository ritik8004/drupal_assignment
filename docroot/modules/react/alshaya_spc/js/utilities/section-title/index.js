import React from 'react';

const SectionTitle = ({ animationDelayValue, children }) => <div className="spc-checkout-section-title fadeInUp" style={{ animationDelay: animationDelayValue }}>{children}</div>;

export default SectionTitle;
