(function($) {
    "use strict"; 
    jQuery(document).on("click", ".cp-overlay-close", function(e){
        if( !jQuery(this).hasClass('do_not_close') ){
            var modal       =  $(this).parents(".cp-overlay"),
                cp_tooltip  =  modal.find(".cp-tooltip-icon").data('classes');
            jQuery(document).trigger('closeModal',[modal]);
            jQuery('head').append('<style id="cp-tooltip-close-css">.tip.'+cp_tooltip+'{ display:none; }</style>');
        }
    });

    jQuery(document).on("click", ".cp-overlay", function(e){
        if( !jQuery(this).hasClass('do_not_close') && jQuery(this).hasClass('close_btn_nd_overlay') ){
            var modal       = jQuery(this);
            jQuery(document).trigger('closeModal',[modal]);
        }
    });

    jQuery(document).on("click", ".cp_fs_overlay", function(e){
       if( !jQuery(this).parents(".cp-overlay").hasClass('do_not_close') && jQuery(this).parents(".cp-overlay").hasClass('close_btn_nd_overlay') ){
            var modal       = jQuery(this).parents(".cp-overlay");
            jQuery(document).trigger('closeModal',[modal]);
        }
    })

    jQuery(document).on("click", ".cp-overlay .cp-modal", function(e){
        e.stopPropagation();
    });

    jQuery(document).on('smile_customizer_field_change',function(e){
        CPResponsiveTypoInit();
    });
    
    jQuery(document).on('smile_data_received',function(e,data){
        CPResponsiveTypoInit();
    });

    jQuery(".wpcf7").on('wpcf7:invalid', function(event){ //Contact Form 7 - Height Issue fixed
        cp_column_equilize();
    });

    jQuery(window).on("modalOpen", function(e,data) {

        jQuery("html").addClass("cp-mp-open");

        var close_btn_delay = data.data("close-btnonload-delay");    
        close_btn_delay     = Math.round(close_btn_delay * 1000);    // convert delay time from seconds to miliseconds

        if(close_btn_delay){
            setTimeout( function(){
                data.find('.cp-overlay-close').removeClass('cp-hide-close');
            },close_btn_delay);
        }       

        cp_column_equilize(); // set columns equalized       
        CPModelHeight(); //  Model height
        cp_form_sep_top();
        cp_set_width_svg();
        cp_row_equilize();

        var cp_animate     = data.find('.cp-animate-container'),
            animationclass = cp_animate.data('overlay-animation'),
            animatedwidth  = cp_animate.data('disable-animationwidth'),
            vw             = jQuery(window).width();

        if( vw >= animatedwidth || typeof animatedwidth == 'undefined' ){
            jQuery(cp_animate).addClass("smile-animated "+ animationclass);
        }

        jQuery("#cp-tooltip-close-css").remove();

        // remove scroller if modal is window size
        jQuery('.cp-modal-popup-container').each(function(index, element) {
           var t        = jQuery(element),
            modal       = t.find('.cp-modal');
            if( !modal.hasClass("cp-modal-exceed") ){
                if( modal.hasClass('cp-modal-window-size') ){
                    jQuery('html').addClass('cp-window-viewport');
                }else{
                    jQuery("html").delay(1000).addClass('cp-custom-viewport');
                }
            }
        });

        //for close modal after x  sec of inactive
      var inactive_close_time = data.data('close-after');
        $.idleTimer('destroy');
        if( typeof inactive_close_time !== "undefined" ) {
            inactive_close_time = inactive_close_time*1000;
            jQuery( ".cp-overlay" ).idleTimer( {
                timeout: inactive_close_time,
                idle: false
            });
        }
        if( jQuery(".kleo-carousel-features-pager").length > 0 ){
            setTimeout(function(){
                $(window).trigger('resize');
            },1500);
        }

    });    

    jQuery(document).ready(function(){
        jQuery(document).bind('keydown', function(e) {
            if (e.which == 27) {
                var cp_overlay = jQuery(".cp-open"),
                    modal = cp_overlay;
                if( cp_overlay.hasClass("close_btn_nd_overlay") && !cp_overlay.hasClass("do_not_close") ){
                    jQuery(document).trigger('closeModal',[modal]);
                }
            }
        });
        set_affiliate_link();  // Affiliate settings
        CPResponsiveTypoInit();       
        cp_init_psscroll();
    });

    function cp_init_psscroll() {
        if(jQuery(".cp-overlay").length > 0){
            var count = 0;
            jQuery(".cp-overlay").each(function(index, el) {
                if(!jQuery(this).hasClass('ps-container')){
                    if( !jQuery(this).hasClass('cp-open') ){
                        count++;
                        var old_id= jQuery(this).attr('id');
                        jQuery(this).attr("id",old_id+"-"+count);
                    }
                    var id= jQuery(this).attr('id');
                    if(typeof Ps!='undefined'){
                        Ps.initialize(document.getElementById(id));
                    }
                }
            });
        }
    }

})( jQuery );