site="$1"
target_env="$2"
uri="$3"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

echo "Syncing product categories."
drush8 @$site.$target_env --uri=$uri sync-commerce-cats

echo "Syncing product options."
drush8 @$site.$target_env --uri=$uri sync-commerce-product-options

echo "Requesting for products sync."
drush8 @$site.$target_env --uri=$uri sync-commerce-products en 10 -y
drush8 @$site.$target_env --uri=$uri sync-commerce-products ar 5 -y

echo "Syncing stores."
drush8 @$site.$target_env --uri=$uri sync-stores