<?php

use Miso\Operations;

add_action('admin_menu', 'miso_admin_menu');
add_action('wp_ajax_miso_send_form', 'miso_send_form');

function miso_admin_menu() {
    unregister_setting('miso', 'miso_settings');
    register_setting(
        'miso',
        'miso_settings',
        [
            'type' => 'array',
            'description' => 'Miso Settings',
            'sanitize_callback' => function ($value) {
                return $value;
            },
            'show_in_rest' => false,
        ],
    );
    add_settings_section(
        'miso_settings',
        'Miso Settings',
        function () {
            echo '<p>Settings for Miso Integration</p>';
        },
        'miso',
    );
    add_settings_field(
        'api_key',
        'API Key',
        function () {
            $options = get_option('miso_settings', []);
            $api_key = array_key_exists('api_key', $options) ? $options['api_key'] : '';
            echo '<input type="text" name="miso_settings[api_key]" value="' . $api_key . '" />';
        },
        'miso',
        'miso_settings',
    );
    add_menu_page(
        'Miso',
        'Miso',
        'manage_options',
        'miso',
        'miso_admin_page',
        //'dashicons-admin-site'
    );
}

function miso_admin_page() {
    ?>
    <div class="wrap">
        <h1>Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('miso');
                do_settings_sections('miso');
                submit_button();
            ?>
        </form>
        <h1>Operations</h1>
        <form name="sync-posts">
            <div>
                <?php submit_button('Sync posts', 'primary'); ?>
            </div>
            <input type="hidden" name="action" value="miso_send_form">
            <input type="hidden" name="operation" value="sync-posts">
        </form>
    </div>
    <script>
        const ajax_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
        const ajax_nonce = '<?php echo wp_create_nonce( "secure_nonce_name" ); ?>';
        jQuery(document).ready(($) => {
            $('[name="sync-posts"]').on('submit', (event) => {
                event.preventDefault();
                const $form = $(event.target);
                const $button = $form.find('input[type="submit"]');
                const formData = $form.serializeArray();
                formData.push({ name: '_nonce', value: ajax_nonce });
                $button.prop('disabled', true);
                $.ajax({
                    url: ajax_url,
                    method: 'POST',
                    data: formData,
                    success: (response) => {
                        $button.prop('disabled', false);
                        const data = response.data;
                        console.log(data);
                        alert(data);
                    },
                    error: (response) => {
                        $button.prop('disabled', false);
                        const data = response.responseJSON.data;
                        console.error(data);
                        alert('[Failed] ' + data);
                    },
                });
            });
        });
    </script>
    <?php
}

function miso_send_form() {
    // validate source
    check_ajax_referer('secure_nonce_name', '_nonce');

    $operation = $_POST['operation'] ?? null;
    if ( empty($operation) ) {
        wp_send_json_error('Operation not found', 400);
    }
    switch ($operation) {
        case 'sync-posts':
            miso_sync_posts($_POST);
            break;
        default:
            wp_send_json_error('Unrecognized operation: ' . $operation, 400);
    }
}

function miso_sync_posts(array $args) {
    $logger = new CollectiveLogger();
    try {
        Operations::sync_posts($args, [
            'logger' => $logger,
        ]);
    } catch (\Exception $e) {
        wp_send_json_error($e->getMessage(), 500);
    }
    $message = "Success!\n";
    foreach ($logger->getMessages() as $entry) {
        switch ($entry['type']) {
            case 'success':
                $message .= $entry['message'] . "\n";
                break;
        }
    }
    wp_send_json_success($message);
}

class CollectiveLogger {

    protected $messages;

    public function __construct() {
        $this->messages = [];
    }

    public function success($message) {
        $this->_log('success', $message);
    }

    public function error($message) {
        $this->_log('error', $message);
    }

    public function log($message) {
        $this->_log('log', $message);
    }

    public function debug($message) {
        $this->_log('debug', $message);
    }

    protected function _log($type, $message) {
        return $this->messages[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function getMessages() {
        return $this->messages;
    }

}
