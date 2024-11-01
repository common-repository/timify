(function($) {
    "use strict";
    $(document).ready(function() {

        //shortcode show hide
        var lmSelector='#timify_settings .lm_display_method .regular';
        var rtSelector='#timify_reading_settings .rt_display_method .regular';
        var wcSelector='#timify_word_settings .wc_display_method .regular';
        var pvcSelector='#timify_view_settings .pvc_display_method .regular';
        
        var lmDSelected=$(lmSelector).val();
        var rtDSelected=$(rtSelector).val();
        var wcDSelected=$(wcSelector).val();
        var pvcDSelected=$(pvcSelector).val();

        $('#timify_reading_settings .rt_shortcode_content').hide();
        $('#timify_settings .lm_shortcode_content').hide();
        $('#timify_settings .lm_post_date_selector').hide();
        $('#timify_word_settings .wc_shortcode_content').hide();
        $('#timify_view_settings .pvc_shortcode_content').hide();
        
        if(rtDSelected=='shortcode_content'){
            $('#timify_reading_settings .rt_'+rtDSelected).show();
        }
        if(lmDSelected=='shortcode_content'){
            $('#timify_settings .lm_'+lmDSelected).show();
        }
        if(lmDSelected=='replace_original'){
            $('#timify_settings .lm_post_date_selector').show();
        }
        if(wcDSelected=='shortcode_content'){
            $('#timify_word_settings .wc_'+wcDSelected).show();
        }
        if(pvcDSelected=='shortcode_content'){
            $('#timify_view_settings .pvc_'+pvcDSelected).show();
        }

        $(lmSelector).on('change', function() {
            var lmSelectedVal=$(this).find('option:selected').val();
            if(lmSelectedVal=='shortcode_content'){
                $('#timify_settings .lm_'+lmSelectedVal).show();
            }else{
                $('#timify_settings .lm_shortcode_content').hide();
            }
            if(lmSelectedVal=='replace_original'){
                $('#timify_settings .lm_post_date_selector').show();
            }else{
                $('#timify_settings .lm_post_date_selector').hide();
            }

        });
  
        $(rtSelector).on('change', function() {
            var rtSelectedVal=$(this).find('option:selected').val();
            if(rtSelectedVal=='shortcode_content'){
                $('#timify_reading_settings .rt_'+rtSelectedVal).show();
            }else{
                $('#timify_reading_settings .rt_shortcode_content').hide();
            }
        });

        
        $(wcSelector).on('change', function() {
            var wcSelectedVal=$(this).find('option:selected').val();
            if(wcSelectedVal=='shortcode_content'){
                $('#timify_word_settings .wc_'+wcSelectedVal).show();
            }else{
                $('#timify_word_settings .wc_shortcode_content').hide();
            }
        });

        $(pvcSelector).on('change', function() {
            var pvcSelectedVal=$(this).find('option:selected').val();
            if(pvcSelectedVal=='shortcode_content'){
                $('#timify_view_settings .pvc_'+pvcSelectedVal).show();
            }else{
                $('#timify_view_settings .pvc_shortcode_content').hide();
            }
        });

        //ajax for admin dashboard top notice
        $('body').on('click', '.timify-notice .notice-dismiss', function() {
            $.ajax( {
                url: admin_js.ajaxurl,
                method: "POST",
                data: {
                    action: 'timify_remove_notification'
                }
            });
        });

    });
})(jQuery);