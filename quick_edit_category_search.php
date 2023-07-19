<?php
/*
Plugin Name: Quick Edit Category Search
Plugin URI: https://github.com/take-i/quick_edit_category_search
Description: This plugin adds a category search in the Quick Edit menu.
Version: 1.0.0
Author: Ishizaka Takehiko
Author URI: https://hack.gpl.jp/
License: GPLv2 or later
Text Domain: qecs
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function qecs_admin_footer() {
    $nonce = wp_create_nonce( 'qecs_nonce' );
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#the-list').on('click', '.editinline', function() {
                var fieldHtml = '<label><span class="title"><span class="dashicons dashicons-search"></span></span><span class="input-text-wrap"><input type="text" name="qecs_category" class="ptitle qecs-search" value=""></span></label>';
                var target = $('.inline-edit-row .inline-edit-col-center');
                if (!target.find('.qecs-search').length) {
                    target.prepend(fieldHtml);
                }

                $('.qecs-search').on('keyup', function() {
                    var searchQuery = $(this).val();
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'qecs_search_category',
                            search_query: searchQuery,
                            security: '<?php echo $nonce; ?>',
                        },
                        success: function(response) {
                            var results = JSON.parse(response);
                            var result_ids = results.map(function(category) {
                                return 'category-' + category.id;
                            });

                            // Remove unchecked categories not in results
                            $('.cat-checklist li').each(function() {
                                var category_id = $(this).attr('id');
                                if (!$(this).find('input').prop('checked') && !result_ids.includes(category_id)) {
                                    $(this).remove();
                                }
                            });

                            // Add categories from results not in checklist
                            results.forEach(function(category) {
                                var category_id = 'category-' + category.id;
                                if (!$('#' + category_id).length) {
                                    var category_html = '<li id="' + category_id + '"><label class="selectit"><input value="' + category.id + '" type="checkbox" name="post_category[]" id="in-' + category_id + '"> ' + category.name + '</label></li>';
                                    $('.cat-checklist').append(category_html);
                                }
                            });
                        }
                    });
                });
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'qecs_admin_footer');

add_action('wp_ajax_qecs_search_category', 'qecs_search_category');
function qecs_search_category() {
    check_ajax_referer( 'qecs_nonce', 'security' );
    $search_query = sanitize_text_field($_POST['search_query']);
    $categories = get_categories(array(
        'name__like' => $search_query,
    ));

    $results = array();
    foreach ($categories as $category) {
        $results[] = array(
            'id' => $category->term_id,
            'name' => $category->name,
        );
    }

    echo json_encode($results);
    wp_die();
}
