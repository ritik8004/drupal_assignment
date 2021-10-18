#!/bin/bash

nb_to_keep=5

repos="alshaya@svn-5975.enterprise-g1.hosting.acquia.com:alshaya.git alshaya2@svn-5975.enterprise-g1.hosting.acquia.com:alshaya2.git alshaya3bis@svn-5975.enterprise-g1.hosting.acquia.com:alshaya3bis.git alshaya4@svn-5975.enterprise-g1.hosting.acquia.com:alshaya4.git alshaya5@svn-5975.enterprise-g1.hosting.acquia.com:alshaya5.git alshaya6tmp2@svn-5975.enterprise-g1.hosting.acquia.com:alshaya6tmp2.git alshaya7tmp@svn-5975.enterprise-g1.hosting.acquia.com:alshaya7tmp.git"
for repo in $repos ; do
  i=0
  to_delete=""
  refs=$(git ls-remote -t --refs $repo | grep -o -E "refs/tags/.*$" | sort -r -t '/' -k 3 -V)
  for ref in $refs ; do
    tag=$(echo $ref | cut -d'/' -f3)

    if (( $i < $nb_to_keep )) ; then
      echo "Keeping $tag"
      ((i++))
    else
      echo "Marking $tag as to be deleted"
      if [ "$to_delete" = "" ] ; then
        to_delete="$tag"
      else
        to_delete+=" $tag"
      fi
    fi
  done
  echo

  if [ ! "$to_delete" = "" ] ; then
    read -p "Do you confirm the deletion of '$to_delete' tags for $repo? " -n 1 yn
    echo
    if [ "$yn" = y ] ; then
      for t in $to_delete ; do
        echo "Deleting $t"
        git push --delete $repo $t
      done
    else
      echo "Nothing deleted"
    fi
  fi
done
