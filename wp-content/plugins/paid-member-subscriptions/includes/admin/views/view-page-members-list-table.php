<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the members admin page
 */
?>

<div class="wrap">

    <h2>
        <?php echo $this->page_title; ?>

        <a href="<?php echo esc_url( add_query_arg( array( 'page' => $this->menu_slug, 'subpage' => 'add_subscription' ), admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2"><?php echo __( 'Add New', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo esc_url( add_query_arg( array( 'page' => $this->menu_slug, 'subpage' => 'add_new_members_bulk' ), admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2"><?php echo __( 'Bulk Add New', 'paid-member-subscriptions' ); ?></a>
    </h2>
    <form method="get">
        <input type="hidden" name="page" value="pms-members-page" />
    <?php

        $members_list_table = new PMS_Members_List_Table();
        $members_list_table->prepare_items();
        $members_list_table->views();
        $members_list_table->search_box(__('Search Members'),'pms_search_members');
        $members_list_table->display();
    ?>
    </form>

</div>