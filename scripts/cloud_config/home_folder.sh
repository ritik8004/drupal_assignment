target_stacks=('alshaya6tmp2' 'alshaya7tmp')
target_env_prefixes=('06' '07')
target_env_suffixes="dev dev2 dev3 test qa2 uat pprod live"

for i in "${!target_stacks[@]}"; do
  stack=${target_stacks[i]}
  prefix=${target_env_prefixes[i]}

  for suffix in $target_env_suffixes; do
    rm -rf /tmp/settings
    scp -r $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/settings /tmp
    scp -r /tmp/settings $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$stack

    rm -rf /tmp/simple-oauth
    scp -r $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/simple-oauth /tmp
    scp -r /tmp/simple-oauth $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$stack

    rm -rf /tmp/slack_settings
    scp -r $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/slack_settings /tmp
    scp -r /tmp/slack_settings $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$stack

    rm -rf /tmp/apple-pay-resources
    scp -r $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/apple-pay-resources /tmp
    scp -r /tmp/apple-pay-resources $stack.$prefix$suffix@$stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$stack
  done
done
