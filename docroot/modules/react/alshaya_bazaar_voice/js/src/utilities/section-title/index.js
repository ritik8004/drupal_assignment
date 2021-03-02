import React from 'react';

const SectionTitle = ({ animationDelayValue, children }) => <div className="pdp-write-review-title fadeInUp" style={{ animationDelay: animationDelayValue }}>{children}</div>;

export default SectionTitle;
