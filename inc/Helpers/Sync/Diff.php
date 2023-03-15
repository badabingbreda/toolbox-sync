<?php
namespace ToolboxSync\Helpers\Sync;

class Diff extends \ToolboxSync\Helpers\Sync {

    public function __construct() {

    }

    public static function suggest( $local , $remote , $direction = 'push' ) {

        $suggest = [];
        // get 
        foreach ($local as $local_post) {
            
            // if there supposedly a connection already exists
            if( $local_post[ 'remote_id' ] ) {
                // check if this still exists
                $match = self::filter( $remote , $local_post[ 'remote_id' ]);
                if ($match) {
                    $suggest[] = [
                                    'local' => $local_post[ 'local_id' ],
                                    'remote' => $match[ 'local_id' ],
                                    'suggest' => "push local {$local_post['remote_id']} to remote {$match['local_id']}",
                                    "type" => "existing",
                                ];
                }
            // if no connection exists
            } else {
                $match = self::match($remote, $local_post);
                // try and match
                if ( $match ) {
                    $suggest[] = [
                        'local' => $local_post[ 'local_id' ],
                        'remote' => $match[ 'local_id' ],
                        "suggest" => "push local {$local_post['local_id']} to remote {$match['local_id']}",
                        "type" => "match",
                    ];
                } else {
                    $suggest[] = [
                        'local' => $local_post[ 'local_id' ],
                        'remote' => false,
                        "suggest" => "create new",
                        "type" => "new",
                    ];
                }
            }
        }

        return $suggest;
    }

    private static function filter( $posts , $id ) {
        $filtered = array_filter( $posts , function( $item ) use ($id) {
            return $item[ 'local_id' ] == $id;
        } );
        // if we have a result return it
        if (sizeof($filtered)>0) return $filtered[0];
        // return false
        return false;
    }

    private static function match( $posts , $post ) {
        $filtered = array_filter( $posts , function ($item) use ($post) {
            return ($item[ 'slug' ] == $post[ 'slug' ]) && ($item[ 'title' ] == $post[ 'title' ]);
        });
        $keys = array_keys($filtered);
        // if we have a result return it
        if (sizeof($filtered)>0) return $filtered[$keys[0]];
        // return false
        return false;

    }

}