<?php 

class CM_Cloud_Visitor {

    /** 
     * @var CC_Cloud_API_V1 Cart66 Cloud API class
     */
    public static $cloud;

    public function __construct() {
        self::$cloud = new CC_Cloud_API_V1();
    }

    /**
     * Return an array of memberships and subscriptions for the visitor identified by the given token
     *
     * Example return value
     * Array (
     *        [0] => Array
     *              (
     *                  [sku] => lifetime
     *                  [days_in] => 0
     *                  [status] => active
     *              )
     *
     *        [1] => Array
     *              (
     *                  [sku] => basic
     *                  [days_in] => 50
     *                  [status] => canceled
     *              )
     *
     *        [2] => Array
     *              (
     *                  [sku] => premium
     *                  [days_in] => 50
     *                  [status] => expired
     *              )
     * )
     *
     * @param string $token The logged in member token
     * @param string $status The types of memberships and subscriptions to include (all, active, canceled, expired)
     * @return array
     */
    public function get_memberships( $token, $status='active' ) {
        $memberships = array();

        if ( !empty( $token ) && strlen( $token ) > 3 ) {
            $url = self::$cloud->api . "memberships/$token";
            $headers = array( 'Accept' => 'application/json' );
            $response = wp_remote_get( $url, self::$cloud->basic_auth_header( $headers ) );

            if( self::$cloud->response_ok( $response ) ) {
                $json = $response['body'];
                $all = json_decode( $json, true );
                if ( $status == 'all' ) {
                    $memberships = $all;
                } else {
                    foreach ( $all as $order ) {
                        if ( isset( $order['status'] ) && $order['status'] == $status ) {
                            $memberships[] = $order;
                        }
                    }
                }
            }

            // CC_Log::write( "$url\nReceived membership list: " . print_r( $memberships, true ) );
        }

        return $memberships;
    }
}
