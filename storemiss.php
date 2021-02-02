<?php
    include 'includes/autoloader.inc.php';
    set_time_limit(0);

    //  import the extract data
        $EXTRACT = new ExtractSave();
        $msg = $EXTRACT->import_automation('./asset/');
        echo $msg;
?>