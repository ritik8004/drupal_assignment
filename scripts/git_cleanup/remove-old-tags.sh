#!/bin/bash

nb_to_keep=5

scriptDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
bltDir="$scriptDir/../../blt"

repos=`grep -Ei '@svn-5975.enterprise-g1.hosting.acquia.com' ${bltDir}/blt.yml | sed -r "s/'//g" | sed -r "s/- //g"`

for repo in $repos ; do
  echo "Processing repo $repo."
  i=0
  to_delete=""
  to_keep=""

  # Get the release tags and keep the last ones (using nb_to_keep).
  refs=$(git ls-remote -t --refs $repo | grep -o -E "refs/tags/.*$" | grep -o -E "refs/tags/[0-9]+\.[0-9]+\.[0-9]+-build$" | sort -r -t '/' -k 3 -V)
  for ref in $refs ; do
    tag=$(echo $ref | cut -d'/' -f3)

    if (( $i < $nb_to_keep )) ; then
      echo "Keeping $tag"
      if [ "$to_keep" = "" ] ; then
        to_keep="$tag"
      else
        to_keep+=" $tag"
      fi
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

  # Get the all tags and compare against the to_keep list.
  # grep -o -vE behaves in a different way on BSD and GNU systems. Instead of building a complex solution to
  # workaround, simply compare the list of tags with the list of tags we know we want to keep.
  refs=$(git ls-remote -t --refs $repo | grep -o -E "refs/tags/.*$" | sort -r -t '/' -k 3 -V)
  for ref in $refs ; do
    tag=$(echo $ref | cut -d'/' -f3)

    for keep_tag in $to_keep ; do
      if [ "$tag" = "$keep_tag" ] ; then
        break
      fi
    done

    if [ "$tag" != "$keep_tag" ] ; then
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
    read -p "Do you confirm the deletion of '$to_delete' tags for $repo? " yn
    echo
    if [ "$yn" = y ] ; then
      for t in $to_delete ; do
        echo "Deleting $t"
        git push --delete $repo $t
      done

      git remote prune $repo
    else
      echo "Nothing deleted"
    fi
  fi
done
