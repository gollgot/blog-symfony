$(document).ready(function(){

    /*
    * DELETE FORM HANDLING
    */
    //Click on delete button of a delete form */
    $('.btn-delete').click(function(event){
        event.preventDefault(); // prevent the default event (sending)
        // Get the modal and open it
        var modal = $('[data-remodal-id=delete-modal]').remodal();
        modal.open();
    });
    // Clic on confirm button of the delete modal
    $(document).on('confirmation', '.delete-modal', function () {
        // Submit the form
        var form = $('.btn-delete').parent('form');
        form.submit();
    });


});