jQuery(document).ready(function($) {
    $metabox = $('#sc_event_venues');
    $formTable = $metabox.find('.form-table');
    $dropdown = $('#sc_event_saved_venue');
    initialVal = $dropdown.val();
    $dropdown.on('change', function(e) {
        val = $(this).val();
        if( val == -1 ) {
            $formTable.removeClass('hide-new-venue-fields');
        } else {
            $formTable.addClass('hide-new-venue-fields');
        }

        if( val == initialVal && val > 0 ) {
            $formTable.addClass('show-venue-details')
        } else {
            $formTable.removeClass('show-venue-details');
        }
    });
});

jQuery(document).ready(function($) {
    $metabox = $('#sc_event_organizers');
    $formTable = $metabox.find('.form-table');
    $dropdown = $('#sc_event_saved_organizer');
    initialVal = $dropdown.val();
    $dropdown.on('change', function(e) {
        val = $(this).val();
        if( val == -1 ) {
            $formTable.removeClass('hide-new-organizer-fields');
        } else {
            $formTable.addClass('hide-new-organizer-fields');
        }

        if( val == initialVal && val > 0 ) {
            $formTable.addClass('show-organizer-details')
        } else {
            $formTable.removeClass('show-organizer-details');
        }
    });
});
