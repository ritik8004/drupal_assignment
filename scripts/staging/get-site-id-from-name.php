<?php

include_once "soft-stage.php";

echo acsf_get_site_id_from_name($argv[1]) ?: 0;