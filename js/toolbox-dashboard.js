

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


        /**
         * AUTHORIZE
         * 
         * 
         */
        $( '#toolboxsync-authorize' ).on( 'click' , function(e) {

            e.preventDefault();
            // try to get remote site url
            const remotesite = $( '#toolboxsync-remotesite' ).val();
            
            let params = {};
            params = { 
                ...params , 
                ...{ 
                    app_name : window.location.origin,
                    app_id : '',
                    success_url : window.location.origin + '/wp-admin/options-general.php?page=toolboxsync-settings',
                }
            };

            const searchParams = new URLSearchParams(params);

            const app_name = 'recipeplucker';

            
            window.open( remotesite + '/wp-admin/authorize-application.php?' + searchParams  , '_blank' );
            //alert( remotesite + '/wp-admin/authorize-application.php' );

        } );

        /**
         * PUSH ACTIONS
         * 
         */
        $( '#tsync-actions-push' ).on( 'click' , function(e) {

            // bubble
            if ( e.target.id == 'push_posts' ) {
                e.preventDefault();
                // collect data to send
                const rows = document.querySelectorAll( '#tsync-local-posts tr[data-id]' );
                let selected = [];
                rows.forEach((v) => {
                    if ( v.querySelector( 'input[type="checkbox"]' ).checked ) {

                        selected.push( { 
                            local : v.querySelector( 'input[type="checkbox"]' ).value,
                            remote : v.querySelector('select').value,
                        } );
                    }
                });

                function ajaxRequest (settings) {

                    if (settings.length > 0) {

                        let current = settings.pop();
                        let selected = document.querySelector( '#tsync-actions-push tr[data-id="'+current.local+'"]' );
                        selected.style.backgroundColor = 'orange';

                        $.ajax({
                            type: 'POST',
                            url: `/wp-admin/admin-ajax.php?action=tsync_push_item`,
                            data: current,
                        }).success(function( response ) {
                             selected.style.backgroundColor = 'lightgreen';
                             ajaxRequest(settings);
                        })
                        .error(function(response){
                            selected.style.backgroundColor = 'red';
                            ajaxRequest(settings);
                        })
                        .done(function (result) {
                        });
                    }
                }
                
                ajaxRequest( selected.reverse() );

            }

            // bubble
            if ( e.target.id == 'de_select_all-push' ) {
                // e.preventDefault();
                const state = e.target.checked;
    
                const rows = document.querySelectorAll( '#tsync-local-posts input[name^="push"]' );
    
                rows.forEach( v => { v.checked = state; } );
    
    
    
            }
        } );


        /**
         * PULL ACTIONS
         * 
         */
        $( '#tsync-actions-push' ).on( 'click' , function(e) {

            // bubble
            if ( e.target.id == 'de_select_all-pull' ) {
                // e.preventDefault();
                const state = e.target.checked;
                const rows = document.querySelectorAll( '#tsync-local-posts input[name^="pull"]' );
                rows.forEach( v => { v.checked = state; } );
    
            }
            
        });


        /**
         * Click on get synceable status for PUSH action
         * 
         */
        $( '#toolboxsync-getremote-push' ).on( 'click' , function(e) {
            e.preventDefault();
            const actions = document.querySelector( '#tsync-actions-push' );
            const de_select_all = document.querySelector( 'template[class="tsync-de_select_all-push"]' ).content.cloneNode(true);
            const rowtemplate = document.querySelector( 'template[class="tsync-row-push"]' );
            const button = document.querySelector( 'template[class="tsync-button-push"]' ).content.cloneNode(true);
            const posttype = document.querySelector( '#toolboxsync-posttype-push' ).value;
            actions.innerHTML = '';

            jQuery.ajax( {
                type: 'GET',
                url: `/wp-admin/admin-ajax.php?action=tsync_push_prepare`,
                data: {
                    posttype : posttype, 
                },
                error: ( response ) => {
                    console.log( response );
                    alert( 'Couldn\'t connect to remote site or an error occured.' );
                },
                success: (response) => {
                    
                    console.log( response );

                    //const local = response.data.local;
                    //const remote = response.data.remote;
                    

                    const remotetarget = remotedropdown( response.data.remote );
                    const table = document.createElement( 'table' );
                    table.id = 'tsync-local-posts';

                    table.appendChild(de_select_all);                   
                    
                    response.data.suggest.forEach( (item) => {
                        
                        let newRow = rowtemplate.content.cloneNode(true);

                        let local = response.data.local.find( (v) => v.local_id == item.local );
                        let remote = response.data.remote.find( (v) => v.local_id == item.remote );
                        
                        newRow.querySelector( 'tr' ).dataset.id = item.local;
                        newRow.querySelector( '.local-id input' ).value = item.local;
                        newRow.querySelector( '.local-id input' ).id = `source_${item.local}`;
                        if ( item.modified != 'older' ) newRow.querySelector( '.local-id input' ).checked = true;
                        newRow.querySelector( '.local-id label' ).innerHTML = `${item.local} - ${local.title} (${local.slug}) ${local.extra}`;
                        newRow.querySelector( '.local-id label' ).htmlFor = `source_${item.local}`;
                        
                        // clone select
                        let newselect = remotetarget.cloneNode(true);
                        newselect.attributes.name = `target_${item.local}`;
                        if ( item.type === 'existing' || item.type === 'match' ) {
                            newselect.value = remote.local_id;
                        } else {
                            newselect.value = 'new';
                        }
                        
                        
                        newRow.querySelector( '.remote-id' ).appendChild( newselect );
                        
                        table.appendChild( newRow );
                    } );
                    
                    actions.appendChild( table );
                    actions.appendChild( button );
                    
                }
            });

        } );

        /**
         * Click on get synceable status for PUSH action
         * 
         */
        $( '#toolboxsync-getremote-pull' ).on( 'click' , function(e) {
            e.preventDefault();
            alert( 'pulling' );
            const actions = document.querySelector( '#tsync-actions-pull' );
            const de_select_all = document.querySelector( 'template[class="tsync-de_select_all-pull"]' ).content.cloneNode(true);
            const rowtemplate = document.querySelector( 'template[class="tsync-row-pull"]' );
            const button = document.querySelector( 'template[class="tsync-button-pull"]' ).content.cloneNode(true);
            const posttype = document.querySelector( '#toolboxsync-posttype-pull' ).value;
            actions.innerHTML = '';

            jQuery.ajax( {
                type: 'GET',
                url: `/wp-admin/admin-ajax.php?action=tsync_pull_prepare`,
                data: {
                    posttype : posttype, 
                },
                error: ( response ) => {
                    console.log( response );
                    alert( 'Couldn\'t connect to remote site or an error occured.' );
                },
                success: (response) => {
                    console.log( response );

                    const localtarget = remotedropdown( response.data.local );
                    const table = document.createElement( 'table' );
                    table.id = 'tsync-local-posts';

                    table.appendChild(de_select_all);                   
                    
                    response.data.suggest.forEach( (item) => {
                        
                        let newRow = rowtemplate.content.cloneNode(true);

                        let local = response.data.local.find( (v) => v.local_id == item.local );
                        let remote = response.data.remote.find( (v) => v.local_id == item.remote );

                        console.log( remote );
                        
                        newRow.querySelector( 'tr' ).dataset.id = item.local;
                        newRow.querySelector( '.remote-id input' ).value = item.local;
                        newRow.querySelector( '.remote-id input' ).id = `source_${item.local}`;
                        if ( item.modified != 'older' ) newRow.querySelector( '.remote-id input' ).checked = true;
                        if ( remote ) {
                            newRow.querySelector( '.remote-id label' ).innerHTML = `${item.local} - ${remote.title} (${remote.slug}) ${remote.extra}`;
                            newRow.querySelector( '.remote-id label' ).htmlFor = `source_${item.local}`;
                        }
                        
                        // clone select
                        let newselect = localtarget.cloneNode(true);
                        newselect.attributes.name = `target_${item.local}`;
                        if ( item.type === 'existing' || item.type === 'match' ) {
                            newselect.value = remote.local_id;
                        } else {
                            newselect.value = 'new';
                        }
                        
                        
                        newRow.querySelector( '.local-id' ).appendChild( newselect );
                        
                        table.appendChild( newRow );
                    } );
                    
                    actions.appendChild( table );
                    actions.appendChild( button );


                } });

        });


        /**
         * creates select input with options from remote entries array
         * @param {*} remote 
         * @returns 
         */
        function remotedropdown( remote ) {
            let drop = document.createElement( 'select' );
            let option = document.createElement( 'option' );
            option.value = 'new';
            option.innerText = `Create new post`;
            drop.appendChild(option);

            remote.forEach( (v) => {
                let option = document.createElement( 'option' );
                option.value = v.local_id;
                option.innerText = `${v.local_id} - ${v.title} (${v.slug}) ${v.extra}`;
                drop.appendChild(option);
            });

            return drop;
        }

    });

})(jQuery);

