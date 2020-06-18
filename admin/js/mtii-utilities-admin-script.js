(function( $ ) {
    'use strict';
    $(window).load(function() {
        var editOption = $(".edit-option")
        editOption.on('click', editOptionController);


        function editOptionController(e) {
            if($(e.target.previousElementSibling).prop("readonly")==true) {
                $(e.target.previousElementSibling).prop("readonly", false);
                $(e.target).html("Cancel Edit");
                alert ("Please Ensure you click the save Button after Editing");
            } else {
                $(e.target.previousElementSibling).prop("readonly", true);
                $(e.target).html("Edit");
            }
        }
    })
})( jQuery );