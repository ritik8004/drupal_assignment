#!/bin/sh

# Usage: scripts/integration/get-sites.sh
#
# Lists all sites with their IDs and machine names
# Example response:
#
# {"count":"16","sites":[{"id":196,"site":"mcuae","stack_id":1,"domain":"mcuae.alshaya.acsitefactory.com","groups":[101],"site_collection":false,"is_primary":true},{"id":201,"site":"pbksa","stack_id":1,"domain":"pbksa.alshaya.acsitefactory.com","groups":[101],"site_collection":false,"is_primary":true},{"id":246,"site":"mckw","stack_id":1,"domain":"mckw.alshaya.acsitefactory.com","groups":[221],"site_collection":false,"is_primary":true},{"id":251,"site":"pbkw","stack_id":1,"domain":"pbkw.alshaya.acsitefactory.com","site_collection":false,"is_primary":true},{"id":256,"site":"pbae","stack_id":1,"domain":"pbae.alshaya.acsitefactory.com","site_collection":false,"is_primary":true},{"id":291,"site":"dhuae","stack_id":1,"domain":"dhuae.alshaya.acsitefactory.com","site_collection":false,"is_primary":true},{"id":296,"site":"bbwuae","stack_id":1,"domain":"bbwuae.alshaya.acsitefactory.com","groups":[101],"site_collection":false,"is_primary":true},{"id":306,"site":"hmkw","stack_id":1,"domain":"hmkw.alshaya.acsitefactory.com","groups":[221],"site_collection":false,"is_primary":true},{"id":311,"site":"mcksaholding","stack_id":1,"domain":"mcksaholding.alshaya.acsitefactory.com","site_collection":false,"is_primary":true},{"id":321,"site":"bbkw","stack_id":1,"domain":"bbkw.alshaya.acsitefactory.com","groups":[101],"site_collection":false,"is_primary":true}]}

source $(dirname "$0")/includes/global-api-settings.inc.sh

curl 'https://www.alshaya.acsitefactory.com/api/v1/sites' -u $user:$api_key

