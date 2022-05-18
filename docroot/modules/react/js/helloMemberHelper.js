import { hasValue } from './utilities/conditionsUtility';

/**
 * Helper function to check if Hello Member is enabled.
 */
export default function isHelloMemberEnabled() {
  let enabled = false;

  if (hasValue(drupalSettings.hello_member)) {
    enabled = drupalSettings.hello_member.enabled;
  }

  return enabled;
}
