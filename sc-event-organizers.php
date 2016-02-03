<?php
class SC_Event_Organizers {

    private $tax = 'event-organizer';

    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

        add_action( 'event-organizer_add_form_fields', array( $this, 'add_taxonomy_form_fields' ) );
        add_action( 'event-organizer_edit_form_fields', array( $this, 'edit_taxonomy_form_fields' ) );
        add_action( 'created_event-organizer', array( $this, 'save_taxonomy_form_fields' ), 10, 1 );
        add_action( 'edited_event-organizer', array( $this, 'save_taxonomy_form_fields' ), 10, 1 );

        add_action( 'admin_menu', array( $this, 'remove_metabox' ), 10 );
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10 );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10 );
    }

    public function register_taxonomy() {
        $labels = array(
            'name'                       => 'Event Organizers',
            'singular_name'              => 'Event Organizer',
            'menu_name'                  => 'Event Organizers',
            'all_items'                  => 'All Event Organizers',
            'parent_item'                => 'Parent Event Organizer',
            'parent_item_colon'          => 'Parent Event Organizer:',
            'new_item_name'              => 'New Event Organizer Name',
            'add_new_item'               => 'Add New Event Organizer',
            'edit_item'                  => 'Edit Event Organizer',
            'update_item'                => 'Update Event Organizer',
            'view_item'                  => 'View Event Organizer',
            'separate_items_with_commas' => 'Separate event organizers with commas',
            'add_or_remove_items'        => 'Add or remove event organizers',
            'choose_from_most_used'      => 'Choose from the most used',
            'popular_items'              => 'Popular event organizers',
            'search_items'               => 'Search Event Organizers',
            'not_found'                  => 'Not Found',
            'no_terms'                   => 'No event organizers',
            'items_list'                 => 'Event organizers list',
            'items_list_navigation'      => 'Event organizers list navigation',
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
        );
        register_taxonomy( $this->tax, array( 'sc_event' ), $args );
    }

    public function add_taxonomy_form_fields( $taxonomy ) {
    ?>
        <div class="form-field term-group">
            <label for="phone">Phone</label>
            <input type="tel" name="sc_event_organizer_phone" id="phone">
        </div>

        <div class="form-field term-group">
            <label for="website">Website</label>
            <input type="url" name="sc_event_organizer_website" id="website">
        </div>

        <div class="form-field term-group">
            <label for="email">Email</label>
            <input type="email" name="sc_event_organizer_email" id="email">
        </div>
    <?php
    }

    public function edit_taxonomy_form_fields( $term ) {
        $old_data = $this->get_term_meta( $term->term_id );
    ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="phone">Phone</label></th>
            <td><input type="tel" name="sc_event_organizer_phone" id="phone" value="<?php echo esc_attr( $old_data['sc_event_organizer_phone'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="website">Website</label></th>
            <td><input type="url" name="sc_event_organizer_website" id="website" value="<?php echo esc_attr( $old_data['sc_event_organizer_website'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="email">Email</label></th>
            <td><input type="url" name="sc_event_organizer_email" id="email" value="<?php echo esc_attr( $old_data['sc_event_organizer_email'] ); ?>"></td>
        </tr>
    <?php
    }

    public function save_taxonomy_form_fields( $term_id = 0, $data = array() ) {
        // Make sure term_id is an integer greater than 0
        $term_id = intval( $term_id );
        if( !$term_id ) {
            return;
        }

        // Make sure we have data to process. If no data was passed use $_POST instead
        if( !$data || empty( $data ) && isset( $_POST ) ) {
            $data = $_POST;
        }
        if( !$data || empty( $data ) ) {
            return;
        }

        if( isset( $data['sc_event_organizer_phone'] ) ){
            $phone = sanitize_text_field( $data['sc_event_organizer_phone'] );
            update_term_meta( $term_id, 'sc_event_organizer_phone', $phone );
        }

        if( isset( $data['sc_event_organizer_website'] ) ){
            $website = sanitize_text_field( $data['sc_event_organizer_website'] );
            update_term_meta( $term_id, 'sc_event_organizer_website', $website );
        }

        if( isset( $data['sc_event_organizer_email'] ) ){
            $email = sanitize_text_field( $data['sc_event_organizer_email'] );
            update_term_meta( $term_id, 'sc_event_organizer_email', $email );
        }
    }

    public function remove_metabox() {
        remove_meta_box( 'tagsdiv-event-organizer', 'sc_event', 'side' );
    }

    public function add_metabox( $post_type ) {
        if( $post_type != 'sc_event' ) {
            return;
        }

        add_meta_box( 'sc_event_organizers', 'Event Organizers', array( $this, 'metabox' ), $post_type, 'normal', 'low' );
    }

    public function metabox( $post, $box ) {
        // $old_data = geT_old_data( $post->ID );
        $organizer_term_id = $this->get_organizer_term_id( $post->ID );
        $organizer_data = array();
        $table_class = 'form-table';
        if( $organizer_term_id > 0 ) {
            $table_class .= ' hide-new-organizer-fields show-organizer-details';
            $organizer_data = $this->get_term_meta( $organizer_term_id );
        }
        ?>
            <style>
            /* Forgive me father, for I have sinned... */
            .organizer-details,
            .hide-new-organizer-fields .new-organizer-field {
                display: none;
            }
            .show-organizer-details .organizer-details {
                display: block;
            }
            </style>
            <table class="<?php echo $table_class; ?>">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <select name="sc_event_saved_organizer" id="sc_event_saved_organizer">
                                <?php echo $this->get_terms_dropdown( $post->ID ); ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="organizer-details">
                        <td colspan="2"><?php echo $this->the_organizer_details( $post->ID ); ?></td>
                    </tr>
                    <tr class="new-organizer-field">
                        <td>
                            <label for="sc_event_organizer_name">Organizer Name</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_organizer_name" id="sc_event_organizer_name">
                        </td>
                    </tr>
                    <tr class="new-organizer-field">
                        <td>
                            <label for="sc_event_organizer_phone">Phone</label>
                        </td>
                        <td>
                            <input type="tel" name="sc_event_organizer_phone" id="sc_event_organizer_phone">
                        </td>
                    </tr>
                    <tr class="new-organizer-field">
                        <td>
                            <label for="sc_event_organizer_website">Website</label>
                        </td>
                        <td>
                            <input type="url" name="sc_event_organizer_website" id="sc_event_organizer_website">
                        </td>
                    </tr>
                    <tr class="new-organizer-field">
                        <td>
                            <label for="sc_event_organizer_email">Email</label>
                        </td>
                        <td>
                            <input type="email" name="sc_event_organizer_email" id="sc_event_organizer_email">
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
    }

    public function save_metabox( $post_id ) {
        if( isset( $_POST['sc_event_saved_organizer'] ) && !empty( $_POST['sc_event_saved_organizer'] ) ) {
            $organizer_term_id = intval( $_POST['sc_event_saved_organizer'] );
            $organizer_name = sanitize_text_field( $_POST['sc_event_organizer_name'] );

            // Adding new organizer term to event-organizer taxonomy
            if( $organizer_term_id === -1 && $organizer_name ) {
                $new_term_id = wp_insert_term( $organizer_name, $this->tax );
                if( is_array( $new_term_id ) && isset( $new_term_id['term_id'] ) ) {
                    $new_term_id = $new_term_id['term_id'];
                }
                if( is_wp_error( $new_term_id ) && $new_term_id->error_data['term_exists'] ) {
                    $new_term_id = $new_term_id->error_data['term_exists'];
                }

                $this->save_taxonomy_form_fields( $new_term_id );
                $organizer_term_id = intval( $new_term_id );
            }

            $inserted = wp_set_object_terms( $post_id, $organizer_term_id, $this->tax );
        }
    }

    public function pre_get_posts( $query ) {
        // via https://github.com/pippinsplugins/Sugar-Event-Calendar-Lite/blob/master/includes/query-filters.php

        if( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'nav_menu_item' ) {
			return $query;
        }

        if( !is_tax( $this->tax ) ) {
            return $query;
        }
        $query->set( 'orderby', 'meta_value_num' );
        $query->set( 'meta_key', 'sc_event_date_time' );
        $query->set( 'order', 'DESC' );

        if( isset( $_GET['event-display'] ) ) {
            $mode = urldecode( $_GET['event-display'] );
            $query->set( 'meta_value', current_time('timestamp') );
            switch($mode) {
                case 'past':
                    $query->set('meta_compare', '<');
                    break;
                case 'upcoming':
                    $query->set('meta_compare', '>=');
                    break;
            }
        }

        if( isset( $_GET['event-order'] ) ) {
            $order = urldecode( $_GET['event-order'] );
            $query->set( 'order', $order );
        }
    }

    /* Helpers */

    public function get_term_meta( $term_id = false ) {
        $output = array(
            'sc_event_organizer_phone' => '',
            'sc_event_organizer_website' => '',
            'sc_event_organizer_email' => '',
        );
        if( !$term_id ) {
            return $output;
        }

        $data = get_term_meta( $term_id );
        foreach( $data as $key => $val ) {
            if( !isset( $output[ $key ] ) ) {
                continue;
            }

            $output[ $key ] = $val[0];
        }

        if( $num = $output['sc_event_organizer_phone'] ) {
            $output['sc_event_organizer_phone'] = sc_prettify_phone_number( $num );
        }

        return $output;
    }

    public function get_organizer_term_id( $post_id = 0 ) {
        if( !$post_id ) {
            $post = get_post();
            $post_id = $post->ID;
        }

        $organizer_term_id = -1;
        $args = array(
            'fields' => 'ids',
        );
        $organizer_terms = wp_get_object_terms( $post_id, $this->tax, $args );
        if( !is_wp_error( $organizer_terms ) && !empty( $organizer_terms ) ) {
            // If there are more than 1 (for whatever reason) we only want the first term returned
            $organizer_term_id = $organizer_terms[0];
        }

        return $organizer_term_id;
    }

    public function get_terms_dropdown( $post_id = 0 ) {
        $organizer_term_id = $this->get_organizer_term_id( $post_id );
        $output = array( '<option value="-1">Add a New Organizer</option>' );
        $args = array(
            'hide_empty' => false,
        );
        $terms = get_terms( $this->tax, $args );
        foreach( $terms as $term ) {
            $output[] = '<option value="' . intval( $term->term_id ) . '" ' . selected( $term->term_id, $organizer_term_id, false  ) . '>' . $term->name . '</option>';
        }

        return implode( "\n\t", $output );
    }

    public function the_organizer_details( $post_id = 0 ) {
        if( !$post_id ) {
            $post = get_post();
            $post_id = $post->ID;
        }

        $venue_term_id = $this->get_organizer_term_id( $post_id );
        $meta = $this->get_term_meta( $venue_term_id );
        // Remove any empty values...
        $meta = array_filter( $meta );

        $output = [];

        if( isset( $meta['sc_event_organizer_phone'] ) ) {
            $phone = $meta['sc_event_organizer_phone'];
            $tel = preg_replace( '/[^0-9]/', '', $phone );
            $output[] = 'Phone: <a href="tel:' . $tel . '">' . $phone . '</a>';
        }

        if( isset( $meta['sc_event_organizer_website'] ) ) {
            $output[] = 'Website: <a href="' . esc_url( $meta['sc_event_organizer_website'] ) . '">' . $meta['sc_event_organizer_website'] . '</a>';
        }

        if( isset( $meta['sc_event_organizer_email'] ) ) {
            $email = $meta['sc_event_organizer_email'];
            $output[] = 'Email: <a href="mailto:' . $email . '">' . $email . '</a>';
        }

        return implode( '<br>', $output );
    }

    public function get_the_organizer( $event_id = 0 ) {
        if( !$event_id ) {
            $post = get_post();
            $event_id = $post->ID;
        }
        $output = array(
            'name' => '',
            'phone' => '',
            'website' => '',
            'email' => '',
        );
        $organizer_term_id = $this->get_organizer_term_id( $event_id );
        if( !$organizer_term_id || $organizer_term_id < 1 ) {
            return false;
        }
        $organizer_term = get_term( $organizer_term_id, $this->tax );
        $output['name'] = $organizer_term->name;
        $meta = $this->get_term_meta( $organizer_term_id );
        foreach( $output as $key => $val ) {
            $meta_key = 'sc_event_organizer_' . $key;
            if( !isset( $meta[ $meta_key ] ) ) {
                continue;
            }

            $output[ $key ] = $meta[ $meta_key ];
        }

        return apply_filters( 'sc_get_the_organizer', (object) $output, $organizer_term, $event_id );
    }

}
global $sc_event_organizers;
$sc_event_organizers = new SC_Event_Organizers();

function sc_get_the_organizer( $event_id = 0 ) {
    global $sc_event_organizers;
    return $sc_event_organizers->get_the_organizer( $event_id );
}
