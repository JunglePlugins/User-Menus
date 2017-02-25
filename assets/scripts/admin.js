(function ($, $document) {
    "use strict";

    function redirect_type() {
        var $this = $(this),
            $url = $this.parents('.menu-item').find('.nav_item_options-redirect_url');

        if ($this.val() == 'custom') {
            $url.show();
        } else {
            $url.hide();
        }
    }

    function which_users() {
        var $this = $(this),
            $item = $this.parents('.menu-item'),
            $roles = $item.find('.nav_item_options-roles');

        if ($this.val() == 'logged_in') {
            $roles.show();
            $item.addClass('show-insert-button');
        } else {
            $roles.hide();
            $item.removeClass('show-insert-button');
        }
    }


    function toggle_user_codes() {
        $(this).parent().toggleClass('open');
    }


    function reset_user_codes(e) {
        if (e !== undefined && $(e.target).parents('.jpum-user-codes').length) {
            return;
        }

        $('.jpum-user-codes').removeClass('open');
    }

    function insert_user_code() {
        var $this = $(this),
            $input = $this.parents('p').find('input'),
            val = $input.val();

        $input.val(val + "{" + $this.data('code') + "}");
        reset_user_codes();
    }

    function append_user_codes() {
        return $('input.edit-menu-item-title').each(function () {
            var $this = $(this).parents('label'),
                template = _.template($('#tmpl-jpum-user-codes').html());

            if (!$this.parents('p').find('.jpum-user-codes').length) {
                $this.after(template());
            }
        });
    }

    function refresh_all_items() {
        append_user_codes();
        $(".nav_item_options-redirect_type select").each(redirect_type);
        $('.nav_item_options-which_users select').each(which_users);
    }

    $document
        .on('change', '.nav_item_options-redirect_type select', redirect_type)
        .on('change', '.nav_item_options-which_users select', which_users)
        .on('click', '.jpum-user-codes > span', toggle_user_codes)
        .on('click', '.jpum-user-codes li', insert_user_code)
        .on('click', reset_user_codes)
        .ready(refresh_all_items);

    // Add click event directly to submit buttons to prevent being prevented by default action.
    $('.submit-add-to-menu').click(function () {
        setTimeout(refresh_all_items, 1000);
    });

}(jQuery, jQuery(document)));