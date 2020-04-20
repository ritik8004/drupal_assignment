target_stacks=('alshaya' 'alshaya2' 'alshaya3bis')
target_env_prefixes=('01' '02' '01')
target_env_suffix="live"

for i in "${!target_stacks[@]}"; do
  stack=${target_stacks[i]}
  prefix=${target_env_prefixes[i]}

  echo "$stack - $prefix"
  connection="$stack.$prefix$target_env_suffix@$stack$prefix$target_env_suffix.ssh.enterprise-g1.acquia-sites.com"

  scp ~/Downloads/acquia-cloud.drush-8-aliases.tar.gz $connection:/home/$stack
  ssh -t $connection 'tar -C $HOME -xf $HOME/acquia-cloud.drush-8-aliases.tar.gz ; rm $HOME/acquia-cloud.drush-8-aliases.tar.gz'
done
