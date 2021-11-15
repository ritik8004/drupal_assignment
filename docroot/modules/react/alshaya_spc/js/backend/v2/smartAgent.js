import Cookies from 'js-cookie';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const getAgentDataForExtension = () => {
  try {
    let smartAgentData = Cookies.get('smart_agent_cookie');
    if (!hasValue(smartAgentData)) {
      return {};
    }
    smartAgentData = JSON.parse(atob(smartAgentData));

    const extension = {};

    extension.smart_agent_mail = smartAgentData.email;
    extension.smart_agent_name = smartAgentData.name;
    extension.smart_agent_store = smartAgentData.storeCode;
    extension.smart_agent_ip = smartAgentData.clientIP;
    extension.smart_agent_location = `${smartAgentData.lat};${smartAgentData.lng}`;
    extension.smart_agent_user_agent = smartAgentData.userAgent;

    // Multiple data in single field.
    // @todo remove this once Magento supports individual fields.
    extension.smart_agent_email = `${smartAgentData.name};${smartAgentData.email};${smartAgentData.storeCode}`;
    extension.smart_agent_client_ip = `${smartAgentData.clientIP};${smartAgentData.lat};${smartAgentData.lng}`;

    return extension;
  } catch (e) {
    return {};
  }
};

export default getAgentDataForExtension;
