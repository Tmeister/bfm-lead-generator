<?php

	//Create an instance of our package class...
    $leads = new Bfm_Leads_Table();
    //Fetch, prepare, sort, and filter our data...
    $leads->prepare_items();

?>

<div class="wrap">

    <div id="icon-users" class="icon32"><br/></div>

    <h2>Leads</h2>

    <form id="movies-filter" method="get">

        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

        <?php $leads->display() ?>

    </form>

</div>