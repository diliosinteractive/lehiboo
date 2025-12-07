<?php
/**
 * Response Helper Class
 * Formatage standardisé des réponses API
 *
 * @package LeHiboo_Mobile_API
 */

if (!defined('ABSPATH')) {
    exit;
}

class LMA_Response {

    /**
     * Success response
     *
     * @param mixed $data
     * @param int $status
     * @return WP_REST_Response
     */
    public static function success($data = null, $status = 200) {
        $response = array('success' => true);

        if ($data !== null) {
            $response['data'] = $data;
        }

        return new WP_REST_Response($response, $status);
    }

    /**
     * Error response
     *
     * @param string $code
     * @param string $message
     * @param int $status
     * @param array $details
     * @return WP_REST_Response
     */
    public static function error($code, $message, $status = 400, $details = null) {
        $response = array(
            'success' => false,
            'error' => array(
                'code' => $code,
                'message' => $message,
            ),
        );

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return new WP_REST_Response($response, $status);
    }

    /**
     * Convert WP_Error to response
     *
     * @param WP_Error $error
     * @return WP_REST_Response
     */
    public static function from_error($error) {
        $data = $error->get_error_data();
        $status = isset($data['status']) ? $data['status'] : 400;

        $details = null;
        if (isset($data['errors'])) {
            $details = $data['errors'];
        }

        return self::error(
            $error->get_error_code(),
            $error->get_error_message(),
            $status,
            $details
        );
    }

    /**
     * Paginated response
     *
     * @param array $items
     * @param int $page
     * @param int $per_page
     * @param int $total
     * @param string $items_key
     * @return WP_REST_Response
     */
    public static function paginated($items, $page, $per_page, $total, $items_key = 'items') {
        $total_pages = ceil($total / $per_page);

        return self::success(array(
            $items_key => $items,
            'pagination' => array(
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => $total,
                'total_pages' => $total_pages,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1,
            ),
        ));
    }
}
