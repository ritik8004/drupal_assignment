
if [ $# -eq 0 ]
then
        echo "Missing options!"
        echo "(run $0 -h for help)"
        echo ""
        exit 0
fi


while getopts "e:s:p:b:idtah" OPTION; do
        case $OPTION in

                e)
                        envn=$OPTARG
                        ;;
                s)
                        site="$OPTARG-alshaya.acsitefactory.com"
                        ;;
                b)
                        brandName="$OPTARG"
                        ;;
                p)
                        loadsku=$OPTARG
                        ;;
                i)
                        initialSetup="yes"
                        ;;
                d)
                        clearProducts="yes"
                        ;;
                t)
                        transacSetup="yes"
                        ;;
                a)
                        loadProducts="yes"
                        ;;  

                h)
                        echo "Usage:"
                        echo "alshaya_setup.sh -a -e @alshaya.test -s \"whitelabel15.test\" -i -p='testsimple,MY1245"
                        echo "   -e     sf drush alias"
                        echo "   -s     site url on which to execute commmands"
                        echo "   -o     execute old store drush command"
                        echo "   -p     command seperated list of sku's to load"
                        echo "   -i     run inital site setup commands. This includes setup of usernamme and passowrd, uninstall of shield"
                        echo "   -d     clear all existing products. -a to load all of them again"
                        echo "   -a     load or updated all products, categories, options"
                        echo "   -h     help (this output)"
                        exit 0
                        ;;

        esac
done

echo $site;

if [ "$envn" == "" ] || [ "$site" == "" ] ; then
 echo "Missing options!. -e and -s are mandatory"
 echo "(run $0 -h for help)"
 exit 0;
fi


# show site status - This is just for fun.
drush $envn -l $site status; 

if [ "$initialSetup" = "yes" ]
then
	echo "inital setup"
    # drush $envn -l $site aiota;
    drush $envn -l $site apdi --brand_module="$brandName"
	drush $envn -l $site upwd "Site factory admin" --password="AlShAyAU1@123" 
	drush $envn -l $site user-create siteadmin --mail="user3+admin@example.com" --password=AlShAyAU1admin;
	drush $envn -l $site user-add-role "administrator" --name=siteadmin;
	drush $envn -l $site user-create webmaster --mail="user3+webmaster@example.com" --password=AlShAyAU1webmaster;
	drush $envn -l $site user-add-role "webmaster" --name=webmaster;
	drush $envn -l $site cr;
fi

if [ "$transacSetup" = "yes" ]
then
    drush $envn -l $site pm-uninstall shield -y;
    drush $envn -l $site en basic_auth -y;
    drush $envn -l $site sqlq "update users_field_data set name='admin' where name='Site Factory admin'";
    drush $envn -l $site upwd "admin" --password="AlShAyAU1";
fi


if [ "$clearProducts" = "yes" ]
then
	echo "clearing products"
    drush $envn -l $site accd;
fi

if [ "$loadProducts" = "yes" ]
then
    drush $envn -l $site sync-commerce-product-options;
	drush $envn -l $site sync-commerce-cats;
	drush $envn -l $site sync-stores;
	drush $envn -l $site acspm;
    drush $envn -l $site queue-run acq_promotion_attach_queue;
    drush $envn -l $site queue-run acq_promotion_detach_queue;

	if [ "$loadsku" != "" ]
	then
	   echo "loading product skus"
	   # drush $envn -l $site sync-commerce-products --skus="$loadsku"
       drush $envn -l $site acsp en 30 --skus="$loadsku"
       drush $envn -l $site acsp ar 15 --skus="$loadsku"
	fi
	echo "loading all products"
	# drush $envn -l $site sync-commerce-products;
    drush $envn -l $site acsp en 30;
    drush $envn -l $site acsp ar 15;
fi