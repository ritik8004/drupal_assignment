source_stacks=('alshaya' 'alshaya4' 'alshaya5' 'alshaya7tmp')
source_env_prefixes=('01' '04' '05' '07')
target_stack="alshayadc1"
target_env_prefix="02"
target_env_suffixes="dev dev2 dev3 test qa2 uat pprod live"

for i in "${!source_stacks[@]}"; do
  source_stack=${source_stacks[i]}
  prefix=${source_env_prefixes[i]}

  for suffix in $target_env_suffixes
  do
    rm -rf /tmp/settings
    scp -r $source_stack.$prefix$suffix@$source_stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$source_stack/settings /tmp
    scp -r /tmp/settings $target_stack.$target_env_prefix$suffix@$target_stack$target_env_prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$target_stack

    rm -rf /tmp/simple-oauth
    scp -r $source_stack.$prefix$suffix@$source_stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$source_stack/simple-oauth /tmp
    scp -r /tmp/simple-oauth $target_stack.$target_env_prefix$suffix@$target_stack$target_env_prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$target_stack

    rm -rf /tmp/slack_settings
    scp -r $source_stack.$prefix$suffix@$source_stack$prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$source_stack/slack_settings /tmp
    scp -r /tmp/slack_settings $target_stack.$target_env_prefix$suffix@$target_stack$target_env_prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$target_stack
  done
done
