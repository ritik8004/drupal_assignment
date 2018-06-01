site="$1"
target_env="$2"
uri="$3"

sleepd=15

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

new_count="0"
while :
do
  sleep $sleepd
  old_count=$new_count
  new_count=$(drush8 @$site.$target_env --uri=$uri sqlq "select count(*) from acq_sku")
  echo "There is now $old_count SKUs, there was $new_count SKUs $sleepd seconds ago."

  if [ $old_count != "0" -a $old_count == $new_count ]
  then
    break
  fi

  echo "Waiting $sleepd seconds for more SKUs to come."
done