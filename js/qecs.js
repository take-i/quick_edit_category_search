jQuery(document).ready(function($) {
    $('input[name="qecs_category"]').on('keyup', function() {
        var search_query = $(this).val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'qecs_search_category',
                search_query: search_query
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
