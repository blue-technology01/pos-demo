$(document).ready(function() {

    // Initialize the modal popup hidden in background
        $("#pm-dialog").dialog({
            autoOpen: false,
            modal: true,
            width: 400,
            resizable: false,
            draggable: false,
            buttons: {
                "Save Method": function() {
                    $("#pm-form").submit();
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });

    // Open the popup using global click
    $(document).on('click', '#open-pm-modal', function(e) {
        e.preventDefault();

        if ($("#pm-form").length) {
            $("#pm-form")[0].reset();
        }

        $("#pm-dialog").dialog("open");
    });

});
