$(document).on('click', '[data-flatpickr-clear-target]', function (e) {
    let pickrInput = $($(this).data('flatpickr-clear-target')).get(0);
    let pickrInstance = pickrInput._flatpickr;

    $(pickrInput).trigger('flatpickr:clearing');

    if (pickrInstance.selectedDates?.length) {
        pickrInstance.clear();
        $(pickrInput).trigger('flatpickr:cleared');
    }
});
