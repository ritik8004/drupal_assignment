target_env="$2"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

// @TODO
env=str_replace($target_env, '01', '')

sites=$(drush8 acsf-tools-list --fields | grep " " | cut -d' ' -f6 | awk NF)

echo "$sites" | while IFS= read -r site
do
  profile="$(drush8 -l $site.$env-alshaya.acsitefactory.com php-eval 'echo drupal_get_profile();')"

  if [ $profile = "alshaya_transac" ]
  then
    ./reset-post-db-copy.sh $site $target_env $site.$env-alshaya.acsitefactory.com
  fi

endloop

drush8 acsf-tools-dump --result-folder=~/backup/post-stage --gzip