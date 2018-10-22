uri="$1"

# Sleep duration (seconds) between counts.
sleepd=15

# Maximum number of loop to execute.
max_loop_count=180

new_count="0"
loop_count=0
while :
do
  sleep $sleepd
  old_count=$new_count
  new_count=$(drush8 --uri=$uri sqlq "select count(*) from acq_sku")
  echo "There is now $new_count SKUs, there was $old_count SKUs $sleepd seconds ago."

  if [ $old_count != "0" -a $old_count == $new_count ]
  then
    echo "SKUs count has not changed since $sleepd seconds. Considering the sync done."
    break
  fi

  if [ $max_loop_count == $loop_count ]
  then
    echo "We have been waiting for SKUs to sync too long now. There is probably an issue with sync on $uri. Break!"
    break
   fi

  echo "Waiting $sleepd seconds for more SKUs to come."
  loop_count=$((loop_count+1))
done