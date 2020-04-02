target_stack="alshaya3bis"
target_env_prefix="01"
target_env_suffixes="dev dev2 dev3 test qa2 uat pprod live"

for suffix in $target_env_suffixes
do
  echo $suffix

  rm -rf /tmp/settings
  scp -r alshaya.01$suffix@alshaya01$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/settings /tmp
  scp -r /tmp/settings $target_stack.$target_env_prefix$suffix@$target_stack$target_env_prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$target_stack

  rm -rf /tmp/simple-oauth
  scp -r alshaya.01$suffix@alshaya01$suffix.ssh.enterprise-g1.acquia-sites.com:/home/alshaya/simple-oauth /tmp
  scp -r /tmp/simple-oauth $target_stack.$target_env_prefix$suffix@$target_stack$target_env_prefix$suffix.ssh.enterprise-g1.acquia-sites.com:/home/$target_stack
done
