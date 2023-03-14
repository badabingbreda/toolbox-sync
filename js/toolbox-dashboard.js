

(function ($) {

    $(document).ready( function() {

        $('#toolbox-tab').jqTabs( { duration: 200 });
        $("#toolbox-tab .jq-tab-menu .jq-tab-title").click( function () {
            // show hash in top-bar
            window.location.hash= $(this).data("tab");
        });

        // change active tab to hash if found
        if ( window.location.hash ) {
            var url = window.location.href,
                tab = url.substring(url.indexOf('#')+1);
        } else {
                tab = 'default';
        }
        // activate the button and content
        $('[data-tab='+tab+']').addClass('active');


        $( '#toolboxsync-authorize' ).on( 'click' , function(e) {

            e.preventDefault();
            // try to get remote site url
            const remotesite = $( '#toolboxsync-remotesite' ).val();
            
            let params = {};
            params = { 
                ...params , 
                ...{ 
                    app_name : 'recipeplucker',
                    app_id : '',
                    success_url : 'https://recipe-plucker.test/wp-admin/options-general.php?page=toolboxsync-settings',
                }
            };

            const searchParams = new URLSearchParams(params);

            const app_name = 'recipeplucker';

            
            window.open( remotesite + '/wp-admin/authorize-application.php?' + searchParams  , '_blank' );
            //alert( remotesite + '/wp-admin/authorize-application.php' );

        } );

        $( '#toolboxsync-getremoteposts' ).on( 'click' , function(e) {
            e.preventDefault();
            jQuery.ajax( {
                type: 'GET',
                url: `/wp-admin/admin-ajax.php?action=toolboxsync_get_posts`,
                data: { 
                },
                success: (data) => {
                    
                    console.log( data );

                    
                }
            });

        } );

    });

})(jQuery);

