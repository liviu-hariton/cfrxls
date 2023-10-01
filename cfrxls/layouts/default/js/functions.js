function saveStreetNo(id, table) {
    var new_value = $("#street-no-" + id + "-value").val();

    $.ajax({
        url: site_url + "ajax/app.php",
        type: 'POST',
        data: 'id=' + id + '&action=update_street_no&new_value=' + new_value + '&table=' + table,
        success: function(html){
            $('#street-no-' + id).html(html);

            var myModalEl = document.getElementById('edit-street-no-' + id);
            var modal = bootstrap.Modal.getInstance(myModalEl);

            modal.hide();
        }
    });
}