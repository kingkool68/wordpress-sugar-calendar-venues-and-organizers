<?php
class SC_Event_Venues {

    public $tax = 'event-venue';

    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

        add_action( 'event-venue_add_form_fields', array( $this, 'add_taxonomy_form_fields' ) );
        add_action( 'event-venue_edit_form_fields', array( $this, 'edit_taxonomy_form_fields' ) );
        add_action( 'created_event-venue', array( $this, 'save_taxonomy_form_fields' ), 10, 1 );
        add_action( 'edited_event-venue', array( $this, 'save_taxonomy_form_fields' ), 10, 1 );

        add_action( 'admin_menu', array( $this, 'remove_metabox' ), 10 );
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10 );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10 );
    }

    public function register_taxonomy() {
        $labels = array(
            'name'                       => 'Event Venues',
            'singular_name'              => 'Event Venue',
            'menu_name'                  => 'Event Venues',
            'all_items'                  => 'All Event Venues',
            'parent_item'                => 'Parent Event Venue',
            'parent_item_colon'          => 'Parent Event Venue:',
            'new_item_name'              => 'New Event Venue Name',
            'add_new_item'               => 'Add New Event Venue',
            'edit_item'                  => 'Edit Event Venue',
            'update_item'                => 'Update Event Venue',
            'view_item'                  => 'View Event Venue',
            'separate_items_with_commas' => 'Separate event venues with commas',
            'add_or_remove_items'        => 'Add or remove event venues',
            'choose_from_most_used'      => 'Choose from the most used',
            'popular_items'              => 'Popular event venues',
            'search_items'               => 'Search Event Venues',
            'not_found'                  => 'Not Found',
            'no_terms'                   => 'No event venues',
            'items_list'                 => 'Event venues list',
            'items_list_navigation'      => 'Event venues list navigation',
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

    public function add_taxonomy_form_fields( $taxonomy ) {
    ?>
        <div class="form-field term-group">
            <label for="address">Address</label>
            <input type="text" name="sc_event_venue_address" id="address">
        </div>

        <div class="form-field term-group">
            <label for="city">City</label>
            <input type="text" name="sc_event_venue_city" id="city">
        </div>

        <div class="form-field term-group">
            <label for="state">State (two-letter abbreviation)</label>
            <input type="text" name="sc_event_venue_state" id="state" size="2" maxlength="2" pattern="[A-Za-z]{2}">
        </div>

        <div class="form-field term-group">
            <label for="zip-code">Zip Code</label>
            <input type="text" name="sc_event_venue_zip_code" id="zip-code" size="5" maxlength="5" pattern="\d*">
        </div>

        <div class="form-field term-group">
            <label for="phone">Phone</label>
            <input type="tel" name="sc_event_venue_phone" id="phone">
        </div>

        <div class="form-field term-group">
            <label for="website">Website</label>
            <input type="url" name="sc_event_venue_website" id="website">
        </div>
    <?php
    }

    public function edit_taxonomy_form_fields( $term ) {
        $old_data = $this->get_term_meta( $term->term_id );
    ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="address">Address</label></th>
            <td><input type="text" name="sc_event_venue_address" id="address" value="<?php echo esc_attr( $old_data['sc_event_venue_address'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="city">City</label></th>
            <td><input type="text" name="sc_event_venue_city" id="city" value="<?php echo esc_attr( $old_data['sc_event_venue_city'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="state">State (two-letter abbreviation)</label></th>
            <td><input type="text" name="sc_event_venue_state" id="state" size="2" maxlength="2" pattern="[A-Za-z]{2}" value="<?php echo esc_attr( $old_data['sc_event_venue_state'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="zip-code">Zip Code</label></th>
            <td><input type="text" name="sc_event_venue_zip_code" id="zip-code" size="5" maxlength="5" pattern="\d*" value="<?php echo esc_attr( $old_data['sc_event_venue_zip_code'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="phone">Phone</label></th>
            <td><input type="tel" name="sc_event_venue_phone" id="phone" value="<?php echo esc_attr( $old_data['sc_event_venue_phone'] ); ?>"></td>
        </tr>

        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="website">Website</label></th>
            <td><input type="url" name="sc_event_venue_website" id="website" value="<?php echo esc_attr( $old_data['sc_event_venue_website'] ); ?>"></td>
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

        if( isset( $data['sc_event_venue_address'] ) ){
            $address = sanitize_text_field( $data['sc_event_venue_address'] );
            update_term_meta( $term_id, 'sc_event_venue_address', $address );
        }

        if( isset( $data['sc_event_venue_city'] ) ){
            $city = sanitize_text_field( $data['sc_event_venue_city'] );
            update_term_meta( $term_id, 'sc_event_venue_city', $city );
        }

        if( isset( $data['sc_event_venue_state'] ) ){
            $state = sanitize_text_field( $data['sc_event_venue_state'] );
            $state = strtoupper( substr( $state, 0, 2 ) );
            update_term_meta( $term_id, 'sc_event_venue_state', $state );
        }

        if( isset( $data['sc_event_venue_zip_code'] ) ){
            $zip = sanitize_text_field( $data['sc_event_venue_zip_code'] );
            update_term_meta( $term_id, 'sc_event_venue_zip_code', $zip );
        }

        if( isset( $data['sc_event_venue_phone'] ) ){
            $phone = sanitize_text_field( $data['sc_event_venue_phone'] );
            update_term_meta( $term_id, 'sc_event_venue_phone', $phone );
        }

        if( isset( $data['sc_event_venue_website'] ) ){
            $website = sanitize_text_field( $data['sc_event_venue_website'] );
            update_term_meta( $term_id, 'sc_event_venue_website', $website );
        }
    }

    public function remove_metabox() {
        remove_meta_box( 'tagsdiv-event-venue', 'sc_event', 'side' );
    }

    public function add_metabox( $post_type ) {
        if( $post_type != 'sc_event' ) {
            return;
        }

        add_meta_box( 'sc_event_venues', 'Event Venue', array( $this, 'metabox' ), $post_type, 'normal', 'low' );
    }

    public function metabox( $post, $box ) {
        // $old_data = geT_old_data( $post->ID );
        $venue_term_id = $this->get_venue_term_id( $post->ID );
        $venue_data = array();
        $table_class = 'form-table';
        if( $venue_term_id > 0 ) {
            $table_class .= ' hide-new-venue-fields show-venue-details';
            $venue_data = $this->get_term_meta( $venue_term_id );
        }
        ?>
            <style>
            /* Forgive me father, for I have sinned... */
            .venue-details,
            .hide-new-venue-fields .new-venue-field {
                display: none;
            }
            .show-venue-details .venue-details {
                display: block;
            }
            </style>
            <table class="<?php echo $table_class; ?>">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <select name="sc_event_saved_venue" id="sc_event_saved_venue">
                                <?php echo $this->get_terms_dropdown( $post->ID ); ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="venue-details">
                        <td colspan="2"><?php echo $this->the_venue_details( $post->ID ); ?></td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_name">Venue Name</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_venue_name" id="sc_event_venue_name">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_address">Address</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_venue_address" id="sc_event_venue_address">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_city">City</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_venue_city" id="sc_event_venue_city">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_state">State (two-letter abbreviation)</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_venue_state" id="sc_event_venue_state" size="2" maxlength="2" pattern="[A-Za-z]{2}">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_zip_code">Zip Code</label>
                        </td>
                        <td>
                            <input type="text" name="sc_event_venue_zip_code" id="sc_event_venue_zip_code" size="5" maxlength="5" pattern="\d*">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_phone">Phone</label>
                        </td>
                        <td>
                            <input type="tel" name="sc_event_venue_phone" id="sc_event_venue_phone">
                        </td>
                    </tr>
                    <tr class="new-venue-field">
                        <td>
                            <label for="sc_event_venue_website">Website</label>
                        </td>
                        <td>
                            <input type="url" name="sc_event_venue_website" id="sc_event_venue_website">
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php
    }

    public function save_metabox( $post_id ) {
        if( isset( $_POST['sc_event_saved_venue'] ) && !empty( $_POST['sc_event_saved_venue'] ) ) {
            $venue_term_id = intval( $_POST['sc_event_saved_venue'] );
            $venue_name = sanitize_text_field( $_POST['sc_event_venue_name'] );

            // Adding new venue term to event-venue taxonomy
            if( $venue_term_id === -1 && $venue_name ) {
                $new_term_id = wp_insert_term( $venue_name, $this->tax );
                if( is_array( $new_term_id ) && isset( $new_term_id['term_id'] ) ) {
                    $new_term_id = $new_term_id['term_id'];
                }
                if( is_wp_error( $new_term_id ) && $new_term_id->error_data['term_exists'] ) {
                    $new_term_id = $new_term_id->error_data['term_exists'];
                }

                $this->save_taxonomy_form_fields( $new_term_id );
                $venue_term_id = intval( $new_term_id );
            }

            $inserted = wp_set_object_terms( $post_id, $venue_term_id, $this->tax );
        }
    }

    /* Helpers */

    public function get_term_meta( $term_id = false ) {
        $output = array(
            'sc_event_venue_address' => '',
            'sc_event_venue_city' => '',
            'sc_event_venue_state' => '',
            'sc_event_venue_zip_code' => '',
            'sc_event_venue_phone' => '',
            'sc_event_venue_website' => ''
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

        if( $num = $output['sc_event_venue_phone'] ) {
            $output['sc_event_venue_phone'] = sc_prettify_phone_number( $num );
        }

        return $output;
    }

    public function get_venue_term_id( $event_id = 0 ) {
        if( !$event_id ) {
            $post = get_post();
            $event_id = $post->ID;
        }

        $venue_term_id = -1;
        $args = array(
            'fields' => 'ids',
        );
        $venue_terms = wp_get_object_terms( $event_id, $this->tax, $args );
        if( !is_wp_error( $venue_terms ) && !empty( $venue_terms ) ) {
            // If there are more than 1 (for whatever reason) we only want the first term returned
            $venue_term_id = $venue_terms[0];
        }

        return $venue_term_id;
    }

    public function get_terms_dropdown( $post_id = 0 ) {
        $venue_term_id = $this->get_venue_term_id( $post_id );
        $output = array( '<option value="-1">Add a New Venue</option>' );
        $args = array(
            'hide_empty' => false,
        );
        $terms = get_terms( $this->tax, $args );
        foreach( $terms as $term ) {
            $output[] = '<option value="' . intval( $term->term_id ) . '" ' . selected( $term->term_id, $venue_term_id, false  ) . '>' . $term->name . '</option>';
        }

        return implode( "\n\t", $output );
    }

    public function the_venue_details( $post_id = 0 ) {
        if( !$post_id ) {
            $post = get_post();
            $post_id = $post->ID;
        }

        $venue_term_id = $this->get_venue_term_id( $post_id );
        $meta = $this->get_term_meta( $venue_term_id );
        // Remove any empty values...
        foreach( $meta as $key => $val ) {
            if( !$val ) {
                unset( $meta[ $key ] );
            }
        }

        $output = array();
        if( isset( $meta['sc_event_venue_address'] ) ) {
            $output[] = $meta['sc_event_venue_address'];
        }

        $city_state_zip = '';
        if( isset( $meta['sc_event_venue_city'] ) ) {
            $city_state_zip .= $meta['sc_event_venue_city'];
            if( isset( $meta['sc_event_venue_state'] ) ) {
                $city_state_zip .= ',';
            }
            $city_state_zip .= ' ';
        }
        if( isset( $meta['sc_event_venue_state'] ) ) {
            $city_state_zip .= $meta['sc_event_venue_state'] . ' ';
        }
        if( isset( $meta['sc_event_venue_zip_code'] ) ) {
            $city_state_zip .= $meta['sc_event_venue_zip_code'];
        }

        if( $city_state_zip ) {
            $output[] = trim( $city_state_zip );
        }

        if( isset( $meta['sc_event_venue_phone'] ) ) {
            $phone = $meta['sc_event_venue_phone'];
            $tel = preg_replace( '/[^0-9]/', '', $phone );
            $output[] = '<a href="tel:' . $tel . '">' . $phone . '</a>';
        }

        if( isset( $meta['sc_event_venue_website'] ) ) {
            $output[] = '<a href="' . esc_url( $meta['sc_event_venue_website'] ) . '">' . $meta['sc_event_venue_website'] . '</a>';
        }

        return implode( '<br>', $output );
    }

    public function get_event_venue( $event_id = 0 ) {
        if( !$event_id ) {
            $post = get_post();
            $event_id = $post->ID;
        }

        $venue_term_id = $this->get_venue_term_id( $event_id );
        if( !$venue_term_id || $venue_term_id < 1 ) {
            return new WP_Error( 'no_venue_term', 'The event ID ' . $event_id . ' has no location venue associated with it.' );
        }
        $venue = get_term( $venue_term_id, $this->tax );
        $meta = $this->get_term_meta( $venue_term_id );

        $venue->address = $meta['sc_event_venue_address'];
        $venue->city = $meta['sc_event_venue_city'];
        $venue->state = $meta['sc_event_venue_state'];
        $venue->zip = $meta['sc_event_venue_zip_code'];
        $venue->phone = $meta['sc_event_venue_phone'];
        $venue->website = $meta['sc_event_venue_website'];

        return $venue;
    }

    public function get_event_address( $event_id = 0, $args = array() ) {
        if( !$event_id ) {
            $post = get_post();
            $event_id = $post->ID;
        }

        $defaults = array(
            'separator' => ' ',
            'markup' => '',
        );
        $args = wp_parse_args( $args, $defaults );
        $microformats = false;
        if( $args['markup'] == 'microformats' ) {
            $microformats = true;
        }

        $venue = $this->get_event_venue( $event_id );
        if( is_wp_error( $venue ) ) {
            return '';
        }

        // Remove any empty values...
        foreach( $venue as $key => $val ) {
            if( !$val ) {
                unset( $venue->{ $key } );
            }
        }

        $output = array();
        if( property_exists( $venue, 'address' ) ) {
            $val = $venue->address;
            if( $microformats ) {
                $val = '<span class="p-street-address street-address">' . $val . '</span>';
            }
            $output[] = $val;
        }

        $city_state_zip = '';
        if( property_exists( $venue, 'city' ) ) {
            $val = $venue->city;
            if( $microformats ) {
                $val = '<span class="p-locality locality">' . $val . '</span>';
            }
            $city_state_zip .= $val;
            if( property_exists( $venue, 'state' ) ) {
                $city_state_zip .= ', ';
            }
            $city_state_zip .= ' ';
        }
        if( property_exists( $venue, 'state' ) ) {
            $val = $venue->state;
            if( $microformats ) {
                $val = '<span class="p-region region">' . $val . '</span>';
            }
            $city_state_zip .= $val . ' ';
        }
        if( property_exists( $venue, 'zip' ) ) {
            $val = $venue->zip;
            if( $microformats ) {
                $val = '<span class="p-postal-code postal-code">' . $val . '</span>';
            }
            $city_state_zip .= $val;
        }

        if( $city_state_zip ) {
            $output[] = trim( $city_state_zip );
        }
        $address = trim( implode( $args['separator'], $output ) );
        if( $microformats ) {
            $address = '<span class="h-adr adr">' . $address . '</span>';
        }
        return $address;

    }

}
global $sc_event_venues;
$sc_event_venues = new SC_Event_Venues();

function sc_get_event_venue( $event_id = 0 ) {
    global $sc_event_venues;
    return $sc_event_venues->get_event_venue( $event_id );
}

function sc_get_event_address( $event_id = 0,  $args = array() ) {
    global $sc_event_venues;
    return $sc_event_venues->get_event_address( $event_id, $args );
}
